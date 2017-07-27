# PHP Naive Framework

This framework aims to enable mordern web app development with lagacy version of PHP (>=5.2).

This framework has two main components:
1. Router
2. Database

## Contents
- .htaccess
- Router
    - [Constructor](#router--new-routerbase_path)
    - [\$router->get/post/put/delete(\$path, \$controller)](#router-getpath-controller)
    - [\$router->all(\$path, \$controller)](#router-allpath-controller)
- Database
    - [Constructor](#database--new-databasedbhost-dbname-dbuser-dbpw)
    - [\$database->findAll(\$table)](#database-findalltable)
    - [\$database->findById(\$table, \$id)](#database-findbyidtable-id)
    - [\$database->model(\$table, \$props)](#database-modeltable-props)
    - [\$model->save()](#model-save)
    - [Other supplementary database operations](#other-supplementary-database-operations)

## .htaccess
You must configure apache to route the requests to the entry php file. The following example will serve the files in `public/`, route `api/*` to `api.php` and redirect all other requests to `public/index.html`. This setup is perfect for separating backend and frontend.

You must also allow `PUT` and `DELETE` http requests as demonstrated in the following example.

```
Options +FollowSymLinks
RewriteEngine On

RewriteCond public/$1 -f
RewriteRule ^(.*)$ public/$1 [L]

RewriteCond %{REQUEST_FILENAME} -d [or]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^api.*$ api.php [L]

RewriteCond %{REQUEST_FILENAME} -d [or]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ public/index.html [L]

<Limit GET POST PUT DELETE>
  Allow from all
</Limit>
```

## Router

`classes/Router.cls.php` exposes the `Router` class.

### \$router = new Router(\$base_path='')
`$base_path` defines the root of the application. `$base_path` should not end with `/`. For example, if your app is located at `www.example.com/myapp`, then `$router = new Router("/myapp");`;

### \$router->get(\$path, \$controller)
### \$router->post(\$path, \$controller)
### \$router->put(\$path, \$controller)
### \$router->delete(\$path, \$controller)
Each method corresponds to each HTTP request methods: `GET`, `POST`, `PUT` and `DELETE`. 

`$path` matches the request URL. For example, `www.example.com/users` has `$path` equal to `/users` (and the Router has `$base_path = ''`). We could also set some route parameters. `/users/:id` will match, for instance, `www.example.com/users/3`.

`$controller` is a string containing the controller function name. The controller will receive a `$request` array containing the relevant information:

1. `$request['params']` contains the route parameters
2. `$request['query']` equivalent to `$_GET`
3. `$request['body']` contains the request body
	
This framework can parse 3 different content type of request body:
1. `application/json`
2. `application/x-www-form-urlencoded`
3. `multipart/form-data`

For example:
```php
<?php
function updateUserController($req){
  // PUT /users/:id
  $id = $req['params']['id'];
  $fields = $req['query']['fields'];
  $user = $req['body'];
}

$router = new Router();
$router->put('/users/:id', "updateUserController");
?>
```


### \$router->all(\$path, \$controller)
This special method will accept requests that matches `$path` regardless of the http request method.

## Database

The database class connects to MySQL and serves as an ORM, meaning that you can interact with the database with object models avoiding the need to manually construct SQL.

### \$database = new Database(\$dbhost, \$dbname, \$dbuser, \$dbpw)
This will create connection to the database.

### \$database->findAll(\$table)
This will return an array of Models (see below) representing the rows in the table.

### \$database->findById(\$table, \$id)
This will return a Model representing the row with the corresponding id.

### \$database->model(\$table, \$props)
This will return a Model object. `$props` is an array representing a record in a table. 

For example, if we wish to create a model from the `users` table. 
```php
<?php
$user = $databse->model('users', array(
  "name"    => "Jason Yu",
  "email"   => "me@ycmjason.com",
  "website" => "http://ycmjason.com"
));

echo $user->name; // "Jason Yu"
echo $user->id; // null
$user->email = "ycm.jason@gmail.com"; // you can feel free to change the values.
$user->save() // see below
?>
```

### \$model->save()
`$model->save()` will either insert a record or update the record in(to) the corresponding table. The action is determined by the existance of the `$model->id`. With `$database->findAll($table)` and `$database->findById($table, $id)`, the returned Model(s) will have the id property automatically populated. While in the previous example, since the id is `null`, when `save()` is called, a new record is being inserted.

### Other supplementary database operations
#### \$database->query($sql)
Perform a query to the database and return whatever is returned in the `mysql_query()` call.

#### \$database->create($table, $cols, $vals)
Create a record.

#### \$database->update($table, $id, $cols, $vals)
Update the record with the corresponding id.

## License
MIT
