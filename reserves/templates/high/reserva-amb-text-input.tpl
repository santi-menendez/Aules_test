<h3>RESERVA</h3>
{E_MSG_RESERVA}:<br>
<form action='index.php' method=post>
{E_QUI}: <input type=text name='qui' value='' size=62><br><br>
{E_HORA_INICI}: <input type=text name='hora_inici' value='{HORA_INICI}'>
{E_HORA_FINAL}: <input type=text name='hora_final' value='{HORA_FINAL}'>
<br>
<br>
{E_MOTIU}: <br>
<textarea name='motiu' rows=5 cols=50></textarea><br>
<input type=hidden name='op' value='56'>
<input type=hidden name='recurs' value='{RECURS}'>
<input type=hidden name='dia' value='{DIA}'>
<input type=hidden name='mes' value='{MES}'>
<input type=hidden name='any' value='{ANY}'>
<input type=submit value='reservar'><br>
</form>
