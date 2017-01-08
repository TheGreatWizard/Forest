# o-tree
Simple PHP ORM for tree data structures

# Example

    // database connection configurations
    define('DB_NAME', 'your_mysql_DB');
    define('DB_USER', 'your_mysql_user');
    define('DB_PASSWORD', 'your_mysql_password');
    define('DB_HOST', '127.0.0.1');
    
    // biuld an object that inherit DataObject. 
    $obj = new \DAL\DummyDataObject();
    var_dump($obj);
    
    // get databse handle
    $hld = new \DAL\HighLevelDAL();
    
    // save the data object to database
    $hld->saveDataObject($obj, 1);
    
    // load the data object from database
    $loadedObj = $hld->loadDataObject($obj->getCombinedId(), 1);
    var_dump($loadedObj);
    ``
