<?php

final class FFIWrapper
{

    private \FFI $library;

    public function __construct() {
        try{
            $this->library = \FFI::cdef(
                "
                extern void startProxy(char* cip);
                extern void setDeviceOS(int os);
                extern void setDeviceModel(char* model);
                extern void setInputMode(int mode);
                extern void setDefaultInputMode(int mode);
                extern void putEnv(char* env);
                extern void sendToClient(char* buffer, int length);
                extern void sendToServer(char* buffer, int length);
                typedef _Bool (*onClientPacketSend)(char* payload, int len);
                typedef _Bool (*onServerPacketRecv)(char* payload, int len);
                typedef void (*onServerDisconnected)(int reason);
                typedef void (*onClientDisconnected)(int reason);
                typedef void (*onLogin)();
                typedef void (*onTick)();
                typedef void (*delayed)();
                extern void subscribeOnClientPacketSend(onClientPacketSend fn);
                extern void subscribeOnServerPacketReceive(onServerPacketRecv fn);
                extern void subscribeOnServerDisconnected(onServerDisconnected fn);
                extern void subscribeOnClientDisconnected(onClientDisconnected fn);
                extern void subscribeOnLogin(onLogin fn);
                extern void cancelPacket();
                extern void transferTo(char* cip);
                extern void setTicker(int interval, onTick fn);
                extern void runDelayed(int interval, delayed fn);
                extern void setSkinData(char* based);
                extern void setSkinID(char* id);
                extern char* getPlayerSkinBase64ByEid(unsigned int eid);
                extern char* convertSkinBase64ToPngBase64(char* based);
                "
                , "./anxiety.so");
        }catch(\Exception $exception){
            print($exception->getTraceAsString());
        }
        $this->library->putEnv(json_encode(getenv()));
    }

    public function getLibrary(): \FFI
    {
        return $this->library;
    }

}