<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Untitled Document</title>
</head>

<body>
<article>
  <h1>Routing and Dispatching</h1>
  <p>Routing and dispatching in oxide framework is based on concept of <em>Module</em>.  </p>
  <h2>Routing</h2>
  <p>Front controller will create a default Router object for performing routing operation. Router object can route given Request object into Route object. </p>
  <pre>$router = $this-&gt;getRouter();
$route = $router-&gt;route($request);</pre>
  <p>getRouter() method will return a new Route object if not already provided by setRouter. </p>
  <h3>How routing is done</h3>
  <p>The route() method takes a Request object. It simply uses the request path of the request object to start routing. Here are detail routing process:</p>
  <ol>
    <li>Get the requested path from the Request object {<code>$path = request-&gt;getPath()</code>}</li>
    <li>Breaks the path into an array using slash {<code>$parts = explode('/', $path)</code>}</li>
    <li>Extracts the module, controller, action and params components from the array based on current Routing schema
      <ol>
        <li>Routing schema can be changed by setSchema() method</li>
        <li>Default schema is /module/controller/action/param1/param2...</li>
      </ol>
    </li>
    <li>A new Route object is created with extracted values with any additional value from the Request, such as HTTP method name.</li>
    <li></li>
  </ol>
  <h2>What are Modules</h2>
  <p>Modules are core concept of oxide framework. Every web request must be handled by a module. Modules are self contained component that manage a set of related web requests. <strong>Conceptually modules are MVC component</strong>. It provides set of Controllers, Views and Models to handle specific requests. Therefore, technically modules are just a directory containing controller, views, models and any other related files and libraries.</p>
  <h3>Module structure</h3>
  <p>As noted before, module is a directory. contents of modules are hover must be property structured with dispatching specification. By default Dispatcher provides </p>
  <p>Here is a sample module directory structure for default dispatching:</p>
  <pre>module
	controller
		DefaultController.php
		AnotherController.php
	view
		default
			index.phtml
			another-action.phtml
		another
			index.phtml
	model
		Model.php</pre>
  <p><br>
  </p>
  <h3>How is Modules loaded</h3>
  <p>Dispatcher is responsible for dispatching the Route object to an appropriate controller for requested module. Therefore, dispatcher provides a set of rules how mudules </p>
</article>
</body>
</html>