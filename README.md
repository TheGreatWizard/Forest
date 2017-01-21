# o-tree
Simple PHP ORM for tree data structures

1. Build your data object inheriting one of predefined DataObject types:
    ![alt tag](https://github.com/TheGreatWizard/OTree/blob/master/src/img/diagram.png)

2. Connect your data objects to form tree structure

3. Save the tree to database

4. Load the tree starting from the node you choose to the depth you choose

# install with composer
    "require": {
          "the-great-wizard/o-tree": "dev-master"
      }

# Example

    // database connection configurations
    define('DB_NAME', 'your_mysql_DB');
    define('DB_USER', 'your_mysql_user');
    define('DB_PASSWORD', 'your_mysql_password');
    define('DB_HOST', '127.0.0.1');
    
    // build an object that inherit DataObject. 
    $obj = new \DAL\DummyDataObject();
    var_dump($obj);
    
    // get database handle
    $hld = new \DAL\HighLevelDAL();
    
    // save the data object to database
    $hld->saveDataObject($obj, 1);
    
    // load the data object from database
    $loadedObj = $hld->loadDataObject($obj->getCombinedId(), 1);
    var_dump($loadedObj);
    
