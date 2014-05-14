<?php
class Controller_Sitemap extends Controller {
	public function index() {
		$dt = new Time();
		$datetime = str_replace(' ','T',$dt->format('Y-m-d H:i:s'))."+07:00";
		header("Content-Type: application/xml");
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		echo "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

		$j = 0;
		for ($i=0; $i < count(Model_Messages::fetch()) ; $i=$i+1000) {
			$j++;
			echo "<sitemap>";
			echo "<loc>http://" .DOMAIN."/sitemap/message/page/$j.xml</loc>";
			echo "<lastmod>$datetime</lastmod>";
			echo "</sitemap>";
		}
		echo "</sitemapindex>";
		exit;
	}

	public function message() {
		$dt       = new Time();
		$datetime = str_replace(' ','T',$dt->format('Y-m-d H:i:s'))."+07:00";
		$limit    = 1000;
		$page     = ((int)get('page')>1)?(int)get('page'):1;
		$offset   = $limit*($page-1);
		header("Content-Type: application/xml");
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
		$ar = Model_Messages::fetch(array(),$limit,$offset);
		foreach ($ar as $a) {
			echo "<url>";
			echo "<loc>http://" .DOMAIN."/".(($a->type=='status')?'c':'p')."/".(($a->link)?$a->link:$a->id).".html</loc>";
			echo "<lastmod>".str_replace(' ','T',$a->date)."+07:00</lastmod>";
			echo "<changefreq>monthly</changefreq>";
			echo "<priority>1.0</priority>";
			echo "</url>";
		}
		echo "</urlset>";
		exit;
	}
}