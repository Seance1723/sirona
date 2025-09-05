$f = 'C:\xampp\htdocs\testpilot\wp-content\mu-plugins\fortiveax-core.php'
$c = Get-Content $f -Raw
if($c -notmatch "function_exists\( 'fx_integrity_check' \)"){
  $pattern = "(?m)^\s*fx_integrity_check\(\);\s*$"
  $replacement = "if ( function_exists( 'fx_integrity_check' ) ) {`r`n    fx_integrity_check();`r`n}"
  $new = [System.Text.RegularExpressions.Regex]::Replace($c, $pattern, $replacement, 1)
  Set-Content -NoNewline -Path $f -Value $new
  Write-Host "Force-wrapped fx_integrity_check() call."
} else {
  Write-Host "Wrapper already present."
}

