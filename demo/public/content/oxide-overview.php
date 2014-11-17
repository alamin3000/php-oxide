<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Oxide Overview</title>
</head>

<body>
<h1>Oxide framework overview</h1>
<p>Here are some of the core concept of oxide framework:</p>
<ul>
  <li>Modular based application development.
    <ul>
      <li>Module is core concept of oxide framework</li>
      <li>Every request must be handled by a module</li>
      <li>An application can have one or more modules</li>
      <li>Modules are self contained MVC components</li>
      <li>Routing and dispatching are based on Route object that provides information about module</li>
    </ul>
  </li>
  <li>Every code components/classes must be part of a namespace  
    <ul>
      <li>Namespaces are replacement for directories</li>
      <li>This way various different standards for directory and namespaces can be used</li>
      <li>By default oxide provides PSR-0 namespace standard</li>
      <li>Namespaces can be manually registered via Loader::registerNamespace($namespace, $dir)</li>
      <li>Or namespaces can be part of componser</li>
      <li>If any class needs actual directory, it should use dirname(__FILE__) or use AbstractClass to get information about directory</li>
    </ul>
  </li>
  <li>Map, Currier and Package (or Route, Dispatcher, Context)</li>
</ul>
<p>&nbsp;</p>
<p>Primary job of the provide packages</p>
<ul>
  <li>http
    <ul>
      <li>handle application request and response</li>
      <li>route the current request and dispatch to the proper module</li>
    </ul>
  </li>
  <li>module
    <ul>
      <li>actual application logics and actions</li>
    </ul>
  </li>
  <li>data
    <ul>
      <li>all persistence data related</li>
    </ul>
  </li>
  <li>ui
    <ul>
      <li>optional</li>
    </ul>
  </li>
  <li>validation
    <ul>
      <li>data validation</li>
    </ul>
  </li>
  <li> </li>
</ul>
</body>
</html>