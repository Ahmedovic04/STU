/*
  ==================================================================================
  نظام استدعاء الطلاب بالبطاقة الذكية RFID
  المكونات: ESP32 DevKitC + قارئ RFID RC522 + جرس تنبيه Buzzer + ليدات إشارة (أخضر وأحمر)
  ==================================================================================
  
  طريقة العمل وتوزيع التنبيهات:
  1. الإضاءة الخضراء: تضيء عند قبول طلب الاستدعاء بنجاح (CALLED_SUCCESS).
  2. الإضاءة الحمراء: تضيء في حالتين:
     - الطالب غير مسجل في النظام (NOT_FOUND).
     - الطالب تم استدعاؤه مسبقاً اليوم (ALREADY_CALLED).
  3. درجات الصوت (3 درجات مميزة من النغمات عبر الـ Buzzer):
     - درجة نجاح الاستدعاء: نغمتان تصاعديتان سريعتان ومبهجتان (1900Hz -> 2500Hz).
     - درجة تم الاستدعاء مسبقاً: 3 نغمات تنبيهية متتالية متوسطة النبرة (1400Hz).
     - درجة لم ينجح الاستدعاء: نغمة واحدة غليظة ومنخفضة النبرة وطويلة (550Hz).

  المكتبات المطلوبة في Arduino IDE:
  - MFRC522 by GithubCommunity (أو miguelbalboa)
  - ArduinoJson by Benoit Blanchon (إصدار 6 أو 7)
  ==================================================================================
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// =================== إعدادات الشبكة والسيرفر ===================
const char* WIFI_SSID     = "YOUR_WIFI_SSID";      // اسم شبكة الواي فاي
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";  // كلمة مرور الواي فاي

// رابط السيرفر الخاص بنظام الاستدعاء (سيرفر Coolify أو سيرفر محلي)
// أمثلة:
// "https://your-school-app.coolify.domain/api/rfid_call.php"
// "http://192.168.1.100/api/rfid_call.php"
const char* SERVER_URL    = "http://192.168.1.100/api/rfid_call.php";

// اسم بوابة أو جهاز الاستدعاء (يظهر في تقارير النظام)
const char* DEVICE_ID     = "بوابة أولياء الأمور 1";

// =================== توصيل الأسلاك (PINOUT) ===================
/*
  توصيل RC522 مع ESP32 DevKitC (SPI Bus):
  -----------------------------------------
  RC522 Pin       ESP32 DevKitC GPIO
  -----------------------------------------
  SDA (SS)   -->  GPIO 5
  SCK        -->  GPIO 18
  MOSI       -->  GPIO 23
  MISO       -->  GPIO 19
  IRQ        -->  (غير متصل / Not Connected)
  GND        -->  GND
  RST        -->  GPIO 22
  3.3V       -->  3V3 (تنبيه حاسم: لا توصله بـ 5V أو VIN نهائياً!)
  -----------------------------------------

  توصيل التنبيهات (Buzzer & LEDs):
  -----------------------------------------
  القطعة                  ESP32 Pin
  -----------------------------------------
  Buzzer (+) جرس موجب  --> GPIO 4
  Buzzer (-) جرس سالب  --> GND
  LED الأخضر (+) موجب   --> GPIO 2 (أو عبر مقاومة 220Ω)
  LED الأخضر (-) سالب   --> GND
  LED الأحمر (+) موجب   --> GPIO 15 (أو عبر مقاومة 220Ω)
  LED الأحمر (-) سالب   --> GND
  -----------------------------------------
*/

#define SS_PIN       5   // SDA
#define RST_PIN      22  // RST

#define BUZZER_PIN   4   // جرس التنبيه (Buzzer)
#define LED_GREEN    2   // ليد أخضر (قبول طلب الاستدعاء)
#define LED_RED      15  // ليد أحمر (غير مسجل أو تم استدعاؤه مسبقاً)

MFRC522 mfrc522(SS_PIN, RST_PIN);

// متغيرات منع تكرار المسح السريع لنفس البطاقة
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 3500; // منع المسح المتكرر لنفس البطاقة لمدة 3.5 ثوانٍ

// =================== وظائف التنبيه الصوتي والضوئي ===================

// 1. نجاح الاستدعاء: إضاءة خضراء + نغمة مبهجة مرتفعة النبرة وتصاعدية
void soundSuccess() {
  digitalWrite(LED_RED, LOW);
  digitalWrite(LED_GREEN, HIGH);
  
  // نغمتان تصاعديتان مبهجتان (درجة النجاح)
  tone(BUZZER_PIN, 1900, 120);
  delay(140);
  tone(BUZZER_PIN, 2500, 180);
  delay(200);
  noTone(BUZZER_PIN);
  digitalWrite(BUZZER_PIN, LOW);
  
  digitalWrite(LED_GREEN, LOW);
}

// 2. تم الاستدعاء مسبقاً: إضاءة حمراء + 3 رنات تنبيهية متتالية متوسطة النبرة
void soundAlreadyCalled() {
  digitalWrite(LED_GREEN, LOW);
  
  // 3 نغمات تنبيهية مميزة مع وميض الليد الأحمر 3 مرات
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_RED, HIGH);
    tone(BUZZER_PIN, 1400, 90);
    delay(110);
    noTone(BUZZER_PIN);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_RED, LOW);
    delay(80);
  }
}

// 3. لم ينجح الاستدعاء / غير مسجل: إضاءة حمراء + نغمة طويلة منخفضة غليظة
void soundError() {
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, HIGH);
  
  // نغمة خطأ عميقة ومنخفضة التردد (درجة فشل الاستدعاء)
  tone(BUZZER_PIN, 550, 650);
  delay(700);
  noTone(BUZZER_PIN);
  digitalWrite(BUZZER_PIN, LOW);
  
  digitalWrite(LED_RED, LOW);
}

// وميض الليد الأحمر أثناء محاولة الاتصال بالواي فاي
void soundWiFiConnecting() {
  digitalWrite(LED_RED, HIGH);
  delay(100);
  digitalWrite(LED_RED, LOW);
  delay(100);
}

// نغمة ترحيبية خفيفة عند تشغيل الجهاز وجاهزيته
void soundStartup() {
  tone(BUZZER_PIN, 1600, 80);
  delay(100);
  tone(BUZZER_PIN, 2200, 120);
  delay(140);
  noTone(BUZZER_PIN);
  digitalWrite(BUZZER_PIN, LOW);
}

// =================== الإعداد الأولي (SETUP) ===================
void setup() {
  Serial.begin(115200);
  delay(500);

  Serial.println("\n==========================================");
  Serial.println("  نظام استدعاء الطلاب الذكي - ESP32 RFID  ");
  Serial.println("==========================================");

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);

  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);

  // تشغيل بروتوكول SPI وقارئ البطاقات
  SPI.begin();
  mfrc522.PCD_Init();
  delay(50);
  mfrc522.PCD_DumpVersionToSerial();

  // الاتصال بشبكة الواي فاي
  Serial.print("جاري الاتصال بالواي فاي: ");
  Serial.println(WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  while (WiFi.status() != WL_CONNECTED) {
    soundWiFiConnecting();
    Serial.print(".");
    delay(300);
  }

  Serial.println("\n[WiFi] متصل بنجاح!");
  Serial.print("[WiFi] عنوان IP الجهاز: ");
  Serial.println(WiFi.localIP());

  // صافرة ترحيبية لجاهزية النظام
  soundStartup();
  Serial.println("النظام جاهز لمسح بطاقات أولياء الأمور...");
}

// =================== إرسال طلب الاستدعاء إلى السيرفر ===================
void sendRfidCall(String cardUid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[خطأ] غير متصل بشبكة الواي فاي! جاري إعادة المحاولة...");
    WiFi.reconnect();
    soundError();
    return;
  }

  HTTPClient http;
  WiFiClientSecure secureClient;
  WiFiClient plainClient;

  // دعم اتصالات HTTP العادية وكذلك اتصالات HTTPS المشفرة (Coolify)
  if (String(SERVER_URL).startsWith("https")) {
    secureClient.setInsecure(); // تخطي فحص البصمة للشهادات
    http.begin(secureClient, SERVER_URL);
  } else {
    http.begin(plainClient, SERVER_URL);
  }

  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000); // مهلة أقصاها 5 ثوانٍ للاستجابة

  // تجهيز حمولة JSON المتوافقة مع ArduinoJson v6 و v7
  #if ARDUINOJSON_VERSION_MAJOR >= 7
    JsonDocument reqDoc;
  #else
    StaticJsonDocument<256> reqDoc;
  #endif

  reqDoc["card_uid"] = cardUid;
  reqDoc["device_id"] = DEVICE_ID;
  reqDoc["action"] = "call";

  String requestBody;
  serializeJson(reqDoc, requestBody);

  Serial.println("\n------------------------------------------");
  Serial.print("[إرسال] مسح بطاقة برقم UID: ");
  Serial.println(cardUid);
  Serial.println("[إرسال] إرسال طلب الاستدعاء للسيرفر...");

  int httpCode = http.POST(requestBody);

  if (httpCode > 0) {
    String response = http.getString();
    Serial.print("[السيرفر] كود الاستجابة HTTP: ");
    Serial.println(httpCode);
    Serial.print("[السيرفر] الرد: ");
    Serial.println(response);

    #if ARDUINOJSON_VERSION_MAJOR >= 7
      JsonDocument resDoc;
    #else
      StaticJsonDocument<512> resDoc;
    #endif

    DeserializationError err = deserializeJson(resDoc, response);

    if (!err) {
      const char* code = resDoc["code"] | "UNKNOWN";
      const char* msg  = resDoc["message"] | "";

      if (strcmp(code, "CALLED_SUCCESS") == 0) {
        const char* studentName = resDoc["student"]["full_name"] | "طالب";
        const char* className   = resDoc["student"]["class_name"] | "";
        Serial.print("🎉 تم استدعاء الطالب بنجاح: ");
        Serial.print(studentName);
        Serial.print(" (");
        Serial.print(className);
        Serial.println(")");
        
        // قبول طلب الاستدعاء: إضاءة خضراء + نغمة نجاح
        soundSuccess();
      } 
      else if (strcmp(code, "ALREADY_CALLED") == 0) {
        Serial.println("ℹ️ الطالب تم استدعاؤه مسبقاً اليوم!");
        
        // تم الاستدعاء مسبقاً: إضاءة حمراء + 3 رنات
        soundAlreadyCalled();
      } 
      else if (strcmp(code, "NOT_FOUND") == 0) {
        Serial.println("⚠️ تنبيه: البطاقة غير مسجلة في قاعدة بيانات المدرسة!");
        
        // بطاقة غير مسجلة: إضاءة حمراء + نغمة فشل منخفضة
        soundError();
      } 
      else {
        Serial.print("تنبيه: ");
        Serial.println(msg);
        soundError();
      }
    } else {
      Serial.println("[خطأ] فشل قراءة JSON من السيرفر!");
      soundError();
    }
  } else {
    Serial.print("[خطأ] فشل الاتصال بالسيرفر! رمز الخطأ: ");
    Serial.println(http.errorToString(httpCode));
    soundError();
  }

  http.end();
}

// =================== الحلقة الرئيسية (LOOP) ===================
void loop() {
  // إعادة الاتصال بالواي فاي تلقائياً عند انقطاعه
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] انقطع الاتصال، جاري المحاولة...");
    WiFi.reconnect();
    delay(2000);
    return;
  }

  // التحقق من وجود بطاقة بالقرب من القارئ
  if (!mfrc522.PICC_IsNewCardPresent()) {
    delay(50);
    return;
  }

  // قراءة بيانات البطاقة
  if (!mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }

  // تحويل UID إلى صيغة Hex نصية (مثال: A1B2C3D4)
  String cardUid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) cardUid += "0";
    cardUid += String(mfrc522.uid.uidByte[i], HEX);
  }
  cardUid.toUpperCase();

  // فحص الكولدوان لمنع التكرار اللحظي للبطاقة الواحدة
  unsigned long now = millis();
  if (cardUid == lastScannedUid && (now - lastScanTime < SCAN_COOLDOWN_MS)) {
    // تم مسحها تواً، تجاهل التكرار
  } else {
    lastScannedUid = cardUid;
    lastScanTime = now;
    // تنفيذ الاستدعاء فوراً!
    sendRfidCall(cardUid);
  }

  // إيقاف تشفير البطاقة الحالية استعداداً للقادمة
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}
