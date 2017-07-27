<?php
function parseBody($raw_body, $raw_content_type){
  $tmp = explode(';', $raw_content_type);
  $contentType = $tmp[0];
  
  $body = null;
  switch($contentType){
    case "application/json":
      $body = json_decode($raw_body, true);
      break;
    case "application/x-www-form-urlencoded":
      parse_str($raw_body, $body);
      break;
    case "multipart/form-data":
      preg_match('/boundary=(.*)$/', $raw_content_type, $matches);
      $boundary = $matches[1];

      $a_blocks = preg_split("/-+$boundary/", $raw_body);
      array_pop($a_blocks);

      $body = array();
      foreach ($a_blocks as $id => $block){
        if (empty($block)) continue;
        if (strpos($block, 'application/octet-stream') !== FALSE){
          preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
        }
        else{
          preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
        }
        $body[$matches[1]] = $matches[2];
      }
      break;
  }
  return $body;
}

class _Route{
  public function __construct($route){
    if($route[0] != '/') die("FATAL: route should start with /");
    $this->route = $route;
    $this->paramNames = $this->_buildParamNames($route);
    $this->regex = $this->_buildRegex($route);
  }

  private function _buildParamNames($route){
    $param_names = array();
    $route_components = explode('/', $route);
    foreach($route_components as $comp){
      if($comp[0] == ':'){
        array_push($param_names, substr($comp, 1));
      }
    }
    return $param_names;
  }

  private function _buildRegex($route){
    $route_components = explode('/', $route);
    foreach($route_components as &$comp){
      if($comp[0] == ':'){
        $comp = '(\w+)';
      }else{
        $comp = preg_quote($comp);
      }
    }
    return '/^' . join('\\/', $route_components) . '$/';
  }

  public function match($r){
    $matches = array();
    if(!preg_match($this->regex, $r, $matches)) return null;

    $params = array();
    foreach($matches as $i => $match){
      if($i == 0) continue;
      $params[$this->paramNames[$i-1]] = $match;
    }

    return $params;
  }
}

class Router{
  public function __construct($base_path=''){
    $this->base = $base_path;
  }

  private function _getBody(){
    $headers = getallheaders();
    $raw_content_type = $headers['Content-Type'];
    $raw_body = file_get_contents("php://input");
    return parseBody($raw_body, $raw_content_type);
  }

  private function _match($route){
    $route = new _Route($this->base . $route);
    return $route->match($_SERVER['REQUEST_URI']); 
  }

  public function get($route, $controller){
    if($_SERVER['REQUEST_METHOD'] != 'GET') return;
    $this->all($route, $controller);
  }

  public function post($route, $controller){
    if($_SERVER['REQUEST_METHOD'] != 'POST') return;
    $this->all($route, $controller);
  }

  public function put($route, $controller){
    if($_SERVER['REQUEST_METHOD'] != 'PUT') return;
    $this->all($route, $controller);
  }

  public function delete($route, $controller){
    if($_SERVER['REQUEST_METHOD'] != 'DELETE') return;
    $this->all($route, $controller);
  }

  public function all($route, $controller){
    $route = new _Route($this->base . $route);
    $params = $route->match($_SERVER['REQUEST_URI']);
    if(is_null($params)) return;

    $request = array(
      "params" => $params,
      "query" => $_GET,
      "body"   => $this->_getBody(),
    );

    call_user_func($controller, $request);
  }
}
