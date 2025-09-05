$f = 'C:\xampp\htdocs\testpilot\wp-content\mu-plugins\fortiveax-core.php'
$lines = Get-Content $f
$out = New-Object System.Collections.Generic.List[string]
$replacedRequire = $false
$wrappedCall = $false
foreach($line in $lines){
  if(-not $replacedRequire -and $line.Trim() -eq "require_once get_template_directory() . '/inc/integrity/checker.php';"){
    $out.Add("`$checker = trailingslashit( get_template_directory() ) . 'inc/integrity/checker.php';") | Out-Null
    $out.Add("if ( file_exists( `$checker ) ) {") | Out-Null
    $out.Add("    require_once `$checker;") | Out-Null
    $out.Add("} else {") | Out-Null
    $out.Add("    add_action( 'admin_init', function() { update_option( 'fortiveax_integrity_fail', 1 ); } );") | Out-Null
    $out.Add("}") | Out-Null
    $replacedRequire = $true
    continue
  }
  if(-not $wrappedCall -and $line.Trim() -eq "fx_integrity_check();"){
    $out.Add("if ( function_exists( 'fx_integrity_check' ) ) {") | Out-Null
    $out.Add("    fx_integrity_check();") | Out-Null
    $out.Add("}") | Out-Null
    $wrappedCall = $true
    continue
  }
  $out.Add($line) | Out-Null
}
[IO.File]::WriteAllLines($f, $out)

Write-Host "Patched fortiveax-core.php successfully."

