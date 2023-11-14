You can add these to your code in order to easily access the classes:

```
function db($db_host = null, $db_name = null, $db_user = null, $db_pass = null)
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\ChaosCRUD\Network\CRUD($db_host, $db_name, $db_user, $db_pass);
    }
    return $instance;
}

function ip()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\ChaosCRUD\Network\IP();
    }
    return $instance;
}

function validate($var)
{
    return JPGerber\ChaosCRUD\System\Input::validate($var);
}
```
