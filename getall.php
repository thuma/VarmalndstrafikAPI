<?php
header('Content-type: application/json; charset=utf-8');

$namelist = 'names.json';
$all = array();
if(is_file($namelist) == FALSE)
	{
	$bokstaver = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','Å','Ä','Ö');
	for($i = 0; $i < count($bokstaver); $i++)
		{
		$newdata = TRUE;
		$page = 0;
		while($newdata)
			{
			$newdata = FALSE;
			$doc = new DOMDocument();
			@$doc->loadHTML(file_get_contents('http://193.45.213.123/kollplatsen/v2/indexes.aspx?optType=0&selKommun=0&Language=se&optFrTo=0&TNSource=VTR&sLetter='.urlencode(utf8_decode($bokstaver[$i])).'&iPage='.$page));
			$list = $doc->getElementById('add-fetch')->getElementsByTagName('a');
			if($list->length == 0)
			{
				break;
			}
			foreach($list as $node)
				{
				$station['name'] = trim($node->nodeValue);
				$station['cleanname'] = preg_replace('/[ |\t]+/', ' ', $station['name']);
				$all[] = $station;
				print $station['name']."\n";
				$newdata = TRUE;
				}
			$page++;
			}
		}
	file_put_contents($namelist,json_encode($all));
	}

// Load all stations into object.
$all = json_decode(file_get_contents($namelist));

$idlist = 'ids.json';
if(is_file($idlist) == FALSE)
	{
	foreach($all as $key => $name)
		{
		$url = 'http://193.45.213.123/kollplatsen/v2/rpajax.aspx?net=VTR&lang=se&letters='.rawurlencode(utf8_decode($name->cleanname));
		$data = utf8_encode(file_get_contents($url));
		$stationer = preg_split('/></',$data);
		foreach($stationer as $soksvar)
			{
			$station = preg_split('/###/', $soksvar);
			$stationinfo = preg_split('/\|/', $station[0]);
			if(trim(preg_replace('/[ |\t]+/', ' ',$stationinfo[0])) == $name->cleanname)
				{
				$all[$key]->id = $stationinfo[1];
				$all[$key]->type = $stationinfo[2];
				print $all[$key]->name.' = '.$all[$key]->id."\n";
				break;
				}
			}
		}
	file_put_contents($idlist,json_encode($all));
	}