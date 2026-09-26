/*
  ==================================================================================
  نظام استدعاء الطلاب بالبطاقة الذكية RFID
  المكونات: ESP32 DevKitC + قارئ RFID RC522 + جرس تنبيه Buzzer + ليدات إشارة
  ==================================================================================
  
  طريقة العمل:
  1. يتصل جهاز ESP32 بشبكة الواي فاي (WiFi) بالمدرسة.
  2. يقوم ولي الأمر بتمرير بطاقته الذكية (RFID) على قارئ RC522 عند البوابة.
  3. يقرأ الـ ESP32 الرقم الفريد للبطاقة (UID) ويرسله فوراً عبر الـ HTTP إلى الخادم.
  4. يتحقق الخادم من اسم الطالب والصف، ويسجل عملية الاستدعاء فوراً.
  5. تظهر التنبيهات في شاشة المعلم تلقائياً خلال أقل من ثانية وبدون أي تدخل بشري!
  6. يعطي جهاز البوابة تأكيداً صوتياً وضوئياً لولي الأمر (نغمة نجاح أو تنبيه).

  المكتبات المطلوبة في Arduino IDE:
  - MFRC522 by GithubCommunity (أو miguelbalboa)
  - ArduinoJson by Benoit Blanchon (إصدار 6 أو 7)
  ==================================================================================
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// =================== إعدادات الشبكة والسيرفر ===================
const char* WIFI_SSID     = "YOUR_WIFI_SSID";      // اسم شبكة الواي فاي
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";  // كلمة مرور الواي فاي

// رابط السيرفر الخاص بنظام الاستدعاء (استبدل بالدومين أو الـ IP الخاص بك)
// مثال: "https://school.coolify.yourdomain.com/api/rfid_call.php"
// أو في الشبكة المحلية: "http://192.168.1.100/api/rfid_call.php"
const char* SERVER_URL    = "http://192.168.1.100/api/rfid_call.php";

// اسم بوابة أو جهاز الاستدعاء
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
  3.3V       -->  3.3V (تنبيه هام جداً: لا توصله بـ 5V نهائياً!)
  -----------------------------------------
*/

#define SS_PIN    5   // SDA
#define RST_PIN   22  // RST

// دبابيس الجرس والليدات للتأكيد الحركي لولي الأمر
#define BUZZER_PIN   4   // جرس التنبيه (Buzzer)
#define LED_GREEN    2   // ليد أخضر (تأكيد الاستدعاء بنجاح)
#define LED_RED      15  // ليد أحمر (بطاقة غير مسجلة أو خطأ)

MFRC522 mfrc522(SS_PIN, RST_PIN);

// متغيرات منع تكرار المسح السريع لنفس البطاقة
String lastScannedUid = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN_MS = 3500; // منع المسح المتكرر لنفس البطاقة لمدة 3.5 ثوانٍ

// =================== وظائف التنبيه الصوتي والضوئي ===================
void soundSuccess() {
  // نغمتان قصيرتان سريعتان ومبهجتان لنجاح الاستدعاء
  digitalWrite(LED_GREEN, HIGH);
  tone(BUZZER_PIN, 2000, 100);
  delay(120);
  tone(BUZZER_PIN, 2600, 150);
  delay(160);
  digitalWrite(LED_GREEN, LOW);
}

void soundAlreadyCalled() {
  // ثلاث نغمات خفيفة: الطالب مستدعى مسبقاً
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_GREEN, HIGH);
    tone(BUZZER_PIN, 1800, 70);
    delay(100);
    digitalWrite(LED_GREEN, LOW);
    delay(60);
  }
}

void soundError() {
  // نغمة طويلة منخفضة للبطاقة غير المعرفة أو الخطأ
  digitalWrite(LED_RED, HIGH);
  tone(BUZZER_PIN, 600, 600);
  delay(650);
  digitalWrite(LED_RED, LOW);
}

void soundWiFiConnecting() {
  digitalWrite(LED_RED, HIGH);
  delay(100);
  digitalWrite(LED_RED, LOW);
  delay(100);
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

  // صافرة ترحيبية جاهزية النظام
  soundSuccess();
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
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(4000); // 4 ثوان كحد أقصى للاستجابة

  // تجهيز حمولة JSON
  StaticJsonDocument<256> reqDoc;
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

    StaticJsonDocument<512> resDoc;
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
        soundSuccess();
      } 
      else if (strcmp(code, "ALREADY_CALLED") == 0) {
        Serial.println("ℹ️ الطالب تم استدعاؤه مسبقاً اليوم!");
        soundAlreadyCalled();
      } 
      else if (strcmp(code, "NOT_FOUND") == 0) {
        Serial.println("⚠️ تنبيه: البطاقة غير مسجلة في قاعدة بيانات المدرسة!");
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
