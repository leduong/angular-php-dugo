<?php
class DB {
	public $pdo=NULL;
	public $type=NULL;
	protected $config=array();
	public static $queries=array();
	public static $last_query=NULL;
	public function __construct(array $config) {
		$this->type=current(explode(':',$config['dns'],2));
		$this->config=$config;
	}
	public function connect() {
		extract($this->config);
		if ($this->type=='mysql'){$this->pdo=new PDO($dns,$username,$password,$params);}
		else {$this->pdo=new PDO($dns);}
		$this->config=NULL;
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	}
	public function quote($value) {
		if(!$this->pdo)
			$this->connect();
		return $this->pdo->quote($value);
	}
	public function column($sql,array $params=NULL,$column=0) {
		return ($statement=$this->query($sql,$params))?$statement->fetchColumn($column):NULL;
	}
	public function row($sql,array $params=NULL,$object=NULL) {
		return (($statement=$this->query($sql,$params))?(($row=$statement->fetch(PDO::FETCH_OBJ))&&$object?new $object($row):$row):NULL);
	}
	public function fetch($sql,array $params=NULL,$column=NULL) {
		return (($statement=$this->query($sql,$params))?($column===NULL?$statement->fetchAll(PDO::FETCH_OBJ):$statement->fetchAll(PDO::FETCH_COLUMN,$column)):NULL);
	}
	public function query($sql,array $params=NULL) {
		$this->type=='mysql'&&$sql=str_replace('"','`',$sql);
		benchmark();
		self::$last_query=$sql;
		$stmt=$this->_query($sql,$params);
		self::$queries[$this->type][]=(benchmark()+array(2 => $sql));
		return $stmt;
	}
	protected function _query($sql,array $params=null) {
		if(!$this->pdo)	$this->connect();
		if($params) {
			$stmt=$this->pdo->prepare($sql);
			$stmt->execute($params);
		}
		else{$stmt=$this->pdo->query($sql);}
		return $stmt;
	}
	public function delete($sql,array $params=NULL) {
		return (($statement=$this->query($sql,$params))?$statement->rowCount():FALSE);
	}
	public function insert($table,$data) {
		$sql='INSERT INTO "'.$table.'" ("'.implode('","',array_keys($data)).'")VALUES('.rtrim(str_repeat('?,',count($data)),',').')';
		return $this->query($sql,array_values($data))?$this->pdo->lastInsertId():0;
	}
	public function update($table,$data,array $where=NULL) {
		$q='UPDATE "'.$table.'" SET "'.implode('" = ?,"',array_keys($data)).'" = ? WHERE ';
		list($a,$b)=self::where($where);
		return (($stmt=$this->query($q.$a,array_merge(array_values($data),$b)))?$stmt->rowCount():NULL);
	}
	public static function select($c,$t,$w=array(),$l=NULL,$o=0,$ord=array()) {
		$s="SELECT $c FROM \"$t\"";
		list($w,$v)=DB::where($w);
		if($w) $s.=" WHERE $w";
		return array($s.DB::order_by($ord).($l?" LIMIT $o,$l":''),$v);
	}
	public static function where(array $where=NULL) {
		$a=$s=array();
		if($where) {
			foreach($where as $c => $v) {
				if(is_int($c)) $s[]=$v;
				else{
					$s[]="\"$c\" = ?";
					$a[]=$v;
				}
			}
		}
		return array(join(' AND ',$s),$a);
	}
	public static function order_by(array $fields=NULL) {
		if($fields) {
			$s=' ORDER BY ';
			foreach($fields as $k => $v) if(is_int($k)) $s.="$v, "; else $s.="\"$k\" $v, ";
			return substr($s,0,-2);
		}
	}
	public static function join($t1,$t2,$f=1,$j='LEFT') {
		return " $j JOIN $t2 ON ".($f?"\"$t1\".\"id\" = \"$t2\".\".{$t1}_id\"":"\"$t1\".\"{$t2}_id\" = \"$t2\".\"id\"");
	}
	public static function in(array $ids) {
		return " in ('".implode("','",array_map('to_int',$ids))."')";
	}
}