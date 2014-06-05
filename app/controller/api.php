<?php
class Controller_Api extends Controller
{
	public function index(){
		if(AJAX_REQUEST){
			if(POST){

			}
		}
		exit;
	}
	public function follows(){
		if(AJAX_REQUEST){
			$city = $group = $topic = $agent = array();
			/*if ($u = @unserialize(cookie::get('user'))){
				$tags = Model_Follows::fetch(array('by' => $u['idu']));
				if ($tags) foreach ($tags as $t) {
					$tag = new Model_Tags($t->tag_id);
					if ($a = Model_City::fetch(array('slug' => $tag->slug),1)) $city[]  = $a[0]->to_array();
					if ($a = Model_Group::fetch(array('slug' => $tag->slug),1)) $group[] = $a[0]->to_array();
					if ($a = Model_Topic::fetch(array('slug' => $tag->slug),1)) $topic[] = $a[0]->to_array();
					if ($a = Model_User::fetch(array('username' => $tag->slug),1)) {
						$user = array();
						foreach($a[0]->to_array() as $k => $v) if ($k != 'password') $user[$k] = $v;
						$agent[] = $user;
					}
				}
			} else {*/
				if ($ar = Model_City::fetch(array(),5,0,array('sort' => 'DESC'))) foreach ($ar as $a) $city[]  = $a->to_array();
				if ($ar = Model_Group::fetch(array(),5,0,array('hits' => 'DESC')))foreach ($ar as $a) $group[] = $a->to_array();
				if ($ar = Model_Topic::fetch(array(),5))foreach ($ar as $a) $topic[] = $a->to_array();
				/*if ($ar = Model_User::fetch(array('idu > 1'),5)) foreach ($ar as $a){
					$user = array();
					foreach($a->to_array() as $k => $v) if ($k != 'password') $user[$k] = $v;
					$agent[] = $user;
				}*/
			//}
			Response::json(array(
				'city' => $city,
				'group' => $group,
				'topic' => $topic,
				'agent' => $agent)
			);
		}
		exit;
	}
	public function stats(){
		if(AJAX_REQUEST){
			$input = input();
			$where = (isset($input->uid))?(array) $input:array();
			$status = Model_Messages::count($where + array('type' => 'status'));
			$realestate = Model_Messages::count($where + array('type' => 'realestate'));
			Response::json(array('stats' => array('status' => $status, 'realestate' => $realestate)));
		}
		exit;
	}
	public function address(){
		if(AJAX_REQUEST){
			$input    = input();
			if(isset($input->address)){
				$array    = $where = array();
				$keywords = explode("-", string::slug($input->address));
				$address  = implode(" ",$keywords);
				$address  = preg_replace('/phuong (\d+)/i', "p$1", $address);
				$address  = preg_replace('/quan (\d+)/i', "q$1", $address);
				$array[]  = array(
								'local' => NULL,
								'tag' => NULL,
								'long_name' => 'Không tìm thấy',
								'map' => array(),
								'check' => FALSE,
								);

				if(strlen($address)>1){
					if(!isset($input->group)){
						foreach ($keywords as $v) $where[] = "slug LIKE '%".string::slug($v)."%'";
						$where = implode(" OR ", $where);
						$fetch = Model_Group::fetch(array("slug LIKE '%".string::slug($input->address)."%'"),10);
						if ($fetch) foreach ($fetch as $f) {
							$array[] = array(
								'local'     => trim($f->local),
								'tag'       => $f->name,
								'long_name' => trim($f->name.", ".$f->address.", ".$f->local),
								'address'   => $f->address,
								'map'       => explode(",",$f->map),
								'check'     => TRUE
								);
						}
					}
					$db = registry('db');
					$query = "SELECT id, MATCH(full_name) AGAINST (? IN BOOLEAN MODE) AS score
							FROM zipcode
							WHERE MATCH(full_name) AGAINST (? IN BOOLEAN MODE)
							ORDER BY `score` DESC
							LIMIT 0, 20";
					$fetch = $db->fetch($query, array($address, $address));
					if ($fetch) foreach ($fetch as $v) {
						$zp = new Model_Zipcode($v->id);
						$district_id = new Model_District($zp->district_id);
						$array[] = array(
							'tag'       => array(),
							'local'     => trim($zp->full_name),
							'long_name' => trim($zp->full_name),
							'address'   => '',
							'map'       => explode(",", $district_id->map),
							'check'     => FALSE
							);
					}
					Response::json(array('results' => $array));
				} else {
					Response::json(array('results' => $array), 404);
				}
			}
		}
		exit;
	}

	public static function check($idu=0){
		$c = unserialize(cookie::get('user'));
		return (controller_admin_index::checklogin()||(is_array($c)&&($idu == $c['idu'])));
	}
}


/*$a = cURL::get(
"http://maps.googleapis.com/maps/api/geocode/json",
array(
	'sensor'     => "false",
	'language'   => "vi",
	'region'     => "VN",
	'components' => "country:VN",
	'address'    => $address
	)
);
if(!$a->error){
$json    = json_decode($a->response);
/*$results = $json->results;
foreach ($results as $value) if (trim($value->formatted_address)!="Việt Nam") {
	$array[] = array(
		'address' => trim(str_replace(", Việt Nam", '', $value->formatted_address)),
		'map' => array($value->geometry->location->lat,$value->geometry->location->lng),
		);
}
foreach ($json->results as $v){
	$ar = (array)$v->address_components;
	$ad = array();
	if (count($ar)>1) foreach ($ar as $a) {
		//die(var_dump( $a->types));
		if (!in_array("country", $a->types)&&!in_array("street_number", $a->types)){
			if (in_array("route", $a->types)){
				$ad_insert = Model_Address::get_or_insert($a->long_name);
			}

			if ((int)$a->long_name>0){
				if (in_array("sublocality", $a->types)){
					$ad[] = "phường ".$a->long_name;
				}
			} else $ad[] = $a->long_name;

		}
	}
	if ($ad){
		$array[] = array(
			'address' => implode(", ", $ad),
			'map' => array($v->geometry->location->lat,$v->geometry->location->lng),
			);
	}
}*/