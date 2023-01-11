<form action='index.php' method=post>
<table>
<tr>
<td>{E_NEW_PASSWORD}:</td><td><input type=password name='password' value='' size=40></td>
</tr>
<tr>
<td>{E_PASSWORD_AGAIN}:</td><td><input type=password name='password_again' value='' size=40></td>
</tr>
</table>
<input type=hidden name='op' value='20'>
<input type=hidden name='log' value='3'>
<input type=submit value='{E_CHANGE}'>

</form>