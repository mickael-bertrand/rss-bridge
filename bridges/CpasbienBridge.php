<?php
class CpasbienBridge extends HttpCachingBridgeAbstract{

    private $request;

	public function loadMetadatas() {

		$this->maintainer = "lagaisse";
		$this->name = "Cpasbien Bridge";
		$this->uri = "http://www.cpasbien.io";
		$this->description = "Returns latest torrents from a request query";

        $this->parameters[] = array(
          'q'=>array(
            'name'=>'Search',
            'required'=>true,
            'title'=>'Type your search'
          )
        );

	}


    public function collectData(array $param){
        $this->loadMetadatas();
        $html = '';
        if (isset($param['q'])) {   /* keyword search mode */
            $this->request = str_replace(" ","-",trim($param['q']));
            $html = $this->getSimpleHTMLDOM($this->uri.'/recherche/'.urlencode($this->request).'.html') or $this->returnServerError('No results for this query.');
        }
        else {
            $this->returnClientError('You must specify a keyword (?q=...).');
        }

        foreach ($html->find('#gauche',0)->find('div') as $episode) {
            if ($episode->getAttribute('class')=='ligne0' || $episode->getAttribute('class')=='ligne1')
            {

                $htmlepisode=str_get_html($this->get_cached($episode->find('a', 0)->getAttribute('href')));

                $item = array();
                $item['author'] = $episode->find('a', 0)->text();
                $item['title'] = $episode->find('a', 0)->text();
                $item['timestamp'] = $this->get_cached_time($episode->find('a', 0)->getAttribute('href'));
                $textefiche=$htmlepisode->find('#textefiche', 0)->find('p',1);
                if (isset($textefiche)) {
                    $item['content'] = $textefiche->text();
                }
                else {
                    $item['content'] = $htmlepisode->find('#textefiche', 0)->find('p',0)->text();
                }

                $item['id'] = $episode->find('a', 0)->getAttribute('href');
                $item['uri'] = $this->uri . $htmlepisode->find('#telecharger',0)->getAttribute('href');
                $this->items[] = $item;
            }
        }


    }


    public function getName(){
        return (!empty($this->request) ? $this->request .' - ' : '') . $this->name;
    }

    public function getCacheDuration(){
        return 60*60*24; // 24 hours
    }
}
