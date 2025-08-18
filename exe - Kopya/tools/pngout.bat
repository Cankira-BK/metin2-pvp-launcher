@echo off
for %%f in (*.png) do (

            echo %%~nf
            pngout.exe "%%~nf.png"
)
echo Finished!