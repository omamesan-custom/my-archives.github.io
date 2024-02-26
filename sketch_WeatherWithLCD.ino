#include <SPI.h>
#include <Ethernet.h>
#include <ArduinoJson.h>
#include <LiquidCrystal_I2C.h>

#define PUSH_BUTTON 4

LiquidCrystal_I2C lcd(0x27, 16 , 2);

char server[] = "api.openweathermap.org";
byte mac[] = {0x20, 0x3C, 0xAE, 0x34, 0x2D, 0xCA};
EthernetClient client;
char rspns[500];
boolean cnnctFlg = false;
boolean doneFlg = false;
StaticJsonDocument<300> doc;
const char * wm;

double temperature = 0;


void initForDebug(void){
  Serial.begin(9600);
  while(!Serial)
    continue;
}

boolean initHardware(void){
  pinMode(PUSH_BUTTON, INPUT_PULLUP);
  return true;
}

boolean initEthernet(void){
  if(!Ethernet.begin(mac))
    return false;
  return true;
}

boolean connectServer(void){
  if (client.connect(server, 80)){
    cnnctFlg = true;
    return true;
  }else{
    cnnctFlg = false;
    return false;
  }
}

void sendCommand(void){
  client.println(F("GET /data/2.5/weather?q=osaka,jp&appid=e7f577ca2c54eafe8c2463724dc1a6fb HTTP/1.1"));
  client.println("Host: " + String(server));
  client.println(F("Connection: close"));
  client.println();
}

boolean readResponse(void) {
  char endOfHeaders[] = "\r\n\r\n";

  if (client.available()) {
    memset(rspns, 0, sizeof(rspns)); // バッファをクリア
    client.readBytesUntil('\r', rspns, sizeof(rspns));
    if (!client.find(endOfHeaders)) {
      Serial.println(rspns);
      return true;
    }
  }
  return false;
}

void disconnect(void){
  if (!client.connected()){
    Serial.println("disconnect.");
    client.stop(); 
  }
}

void analyzeJson(){
  DeserializationError e = deserializeJson(doc, rspns);
  if (e){
    Serial.println(e.code());
    return;
  }
  JsonObject weather = doc["weather"][0];
  wm = weather["main"];    //wmにClearなどの天気情報を格納
  temperature = doc["main"]["temp"];  //変更点！気温情報を抜き出すコード

  return;
}


boolean pushButton(void){
  if(digitalRead(PUSH_BUTTON) == LOW){
    return true;
  }
  return false;
}


void setup(void){
  initForDebug();
  (void)initHardware();
  if(!initEthernet()){
    //Serial.println("Failed to configure Ethernet using DHCP");
    return;
  }

  lcd.init();
  lcd.backlight(); 

  Serial.println("");
  Serial.println(F("IP"));
  Serial.println(Ethernet.localIP());

  lcd.setCursor(0,0);
  lcd.print("Outfit? Push!");
}

void loop() {
  doneFlg = readResponse();
  
  if (doneFlg){
    analyzeJson();
    doneFlg = false;
    disconnect();
    lcd.clear();
    lcd.setCursor(0,0);
    lcd.print("It's ");
    lcd.setCursor(5,0);
    lcd.print(wm);
    temperature -= 273.15;
    lcd.setCursor(0,1);
    lcd.print("temp is ");
    lcd.setCursor(8,1);
    lcd.print(temperature,1);
  }

  if(pushButton()){
    Serial.print(".");
    if(connectServer()){
      Serial.print(F("connect "));
      Serial.println(client.remoteIP());
      sendCommand();
    }
  }
}

