@echo off
7z.exe a -tzip "patcher_assets.zip" .\patcher_assets\* -mx=9 
copy /b metin2_vanilla.exe+patcher_assets.zip World_of_Metin2.exe
upx.exe -9 World_of_Metin2.exe --force
echo Finished packing new Patcher!
pause
