$f = 'C:\xampp\htdocs\testpilot\wp-content\mu-plugins\fortiveax-core.php'
$lines = Get-Content $f
$idx = -1
for($i=0; $i -lt $lines.Count; $i++){
  if($lines[$i].Trim() -eq 'fx_integrity_check();') { $idx = $i; break }
}
if($idx -ge 0){
  $new = New-Object System.Collections.Generic.List[string]
  for($j=0; $j -lt $idx; $j++){ $new.Add($lines[$j]) | Out-Null }
  $new.Add("if ( function_exists( 'fx_integrity_check' ) ) {") | Out-Null
  $new.Add("    fx_integrity_check();") | Out-Null
  $new.Add("}") | Out-Null
  for($j=$idx+1; $j -lt $lines.Count; $j++){ $new.Add($lines[$j]) | Out-Null }
  [IO.File]::WriteAllLines($f, $new)
  Write-Host "Wrapped fx_integrity_check() call."
} else {
  Write-Host "fx_integrity_check() call not found."
}

