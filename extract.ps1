$word = New-Object -ComObject Word.Application
$word.Visible = $false
$docFile = Get-ChildItem -Path . -Filter "*.doc" | Select-Object -First 1
Write-Host "Opening file: $($docFile.FullName)"
$doc = $word.Documents.Open($docFile.FullName)
$text = $doc.Content.Text
$doc.Close()
$word.Quit()
[System.IO.File]::WriteAllText((Join-Path (Get-Location) "extracted_doc_text.txt"), $text, [System.Text.Encoding]::UTF8)
Write-Host "Extraction complete!"
