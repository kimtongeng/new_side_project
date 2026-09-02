$htmlPath = "file:///C:/side%20project/new%20side/project/UltimatePOS-CodeBase-V6.7/scratch/upgrade_doc.html"
$pdfPath1 = "C:\side project\new side\update_ai\Upgrade_Updates_a6ae9cb_to_7a87b36.pdf"
$pdfPath2 = "C:\side project\new side\update_ai\User_Management_Filters_And_Essentials_Module_Updates.pdf"

& "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" --headless --disable-gpu --no-pdf-header-footer --print-to-pdf="$pdfPath1" "$htmlPath"
Start-Sleep -Seconds 2
Copy-Item -Path $pdfPath1 -Destination $pdfPath2 -Force

Get-Item $pdfPath1, $pdfPath2 | Select-Object Name, Length, LastWriteTime
