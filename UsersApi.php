<?php

/**
 * RestServer
 * Implementación de un API REST SERVER muy sencillo
 * siguiendo el enunciado facilitado
 *
 * @author jlballes
 */
class UsersApi
{
	public $method;
	public $params;
	public $format;

	protected static $instance = null;

	/**
     * Protected constructor so nobody else can instance it
     *
     */
    protected function __construct()
    {

    }

    /**
     * Protected __clone so nobody else can clone it
     *
     */
    protected function __clone()
    {

    }

    /**
     * Call this method to get singleton
     *
     * @return Server
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }


    /**
     * Maneja la petición y redirige a la función adecuada
     *
     */
	public function handle()
	{
		$this->params = $this->getPath();
		$this->method = $this->getMethod();
		//construimos nombre de la función con el primer parametro y el metodo
		$func = $this->params[0].ucfirst(strtolower($this->method));

		//si existe la función (metodo en la clase)
		if (method_exists($this, $func)) {
			$this->{$func}($this->params);
		}
		else{
			//TODO: lanzar exception
		}
	}

	/**
     * Obtiene los parametros de la URL
     *
     * Pruebas con ej: localhost/foo/index.php/user/22
     */
	public function getPath()
	{
		$params = str_replace($_SERVER['SCRIPT_NAME'].'/', '', $_SERVER['REQUEST_URI']);
		return preg_split("@[/]@", $params); 
	}

	/**
     * Obtiene el método HTTP de la petición
     */
	public function getMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$override = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : (isset($_GET['method']) ? $_GET['method'] : '');
		if ($method == 'POST' && strtoupper($override) == 'PUT') {
			$method = 'PUT';
		} elseif ($method == 'POST' && strtoupper($override) == 'DELETE') {
			$method = 'DELETE';
		}
		return $method;
	}


	protected function usersGet($params){
		$db = new PDO('mysql:host=localhost;dbname=employees', 'user', 'pass');

		$stmt = $db->query("SELECT e.emp_no, e.first_name, e.last_name, e.hire_date, 
			d.dept_name, t.title, s.salary 
			FROM employees e 
			LEFT JOIN dept_emp de ON e.emp_no=de.emp_no 
			LEFT JOIN departments d ON de.dept_no = d.dept_no 
			LEFT JOIN salaries s ON e.emp_no=s.emp_no 
			LEFT JOIN titles t ON e.emp_no=t.emp_no  
			WHERE de.to_date = '9999-01-01' 
			AND s.to_date = '9999-01-01' 
			AND t.to_date ='9999-01-01'
			ORDER BY e.hire_date
			LIMIT 50
		");

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->sendData($results);
	}

	protected function userGet($params){
		$db = new PDO('mysql:host=localhost;dbname=employees', 'user', 'pass');

		$stmt = $db->prepare("SELECT e.emp_no, e.first_name, e.last_name, e.hire_date, 
			e.gender, e.birth_date, d.dept_name, t.title, s.salary 
			FROM employees e 
			LEFT JOIN dept_emp de ON e.emp_no=de.emp_no 
			LEFT JOIN departments d ON de.dept_no = d.dept_no 
			LEFT JOIN salaries s ON e.emp_no=s.emp_no 
			LEFT JOIN titles t ON e.emp_no=t.emp_no  
			WHERE e.emp_no = ?
			AND de.to_date = '9999-01-01' 
			AND s.to_date = '9999-01-01' 
			AND t.to_date ='9999-01-01'
		");
		$stmt->execute(array($params[1]));

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->sendData($results[0]);
	}
	
	protected function userPost($params){
		$data = file_get_contents('php://input');
		$data = json_decode($data);
		
		try {
		    $db->beginTransaction();
		 
		    $db->exec("SOME QUERY");
		 
		    $stmt = $db->prepare("Insert");
		    $stmt->execute(array($value, ));

		    $db->lastInsertId();


		    //... otro insert
		 
		    $db->commit();
		} catch(PDOException $ex) {
		    //Something went wrong rollback!
		    $db->rollBack();
		    echo $ex->getMessage();
		}
	}
	

	public function sendData($data)
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: 0");
		header('Content-Type: application/json');

		echo json_encode($data);
	}



}