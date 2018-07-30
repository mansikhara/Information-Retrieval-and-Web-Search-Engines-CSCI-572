<?php
ini_set('memory_limit', '-1');
include 'SpellCorrector.php';
include 'parsetrial.php';
header('Content-Type:text/html; charset=utf-8');
$limit = 10;
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;
$flag=0;
$arr = array();
$f = fopen("UrlToHtml_NBCNews.csv","r");
if($f!==false){
while($line = fgetcsv($f,0,","))
{
	
	$key = "/Users/prakharsethi/Desktop/ntemp/solr-7.3.0/crawl_data/".$line['0'];
	$value = $line['1'];
	$arr[$key] = $value;
}
fclose($f);
}
//var_dump($arr);
if($query){
        $query=SpellCorrector::correct($query);
        require_once('solr-php-client-master/Apache/Solr/Service.php');
        $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
        if(get_magic_quotes_gpc() == 1){
                $query = stripslashes($query);
        }
        try{
		if(!isset($_GET['search']))$_GET['search']="lucene";
		if($_GET['search'] == 'lucene'){

			 $results = $solr->search($query, 0, $limit);

		}else{

			$param = array('sort'=>'pageRankFile desc');
			$results = $solr->search($query, 0, $limit, $param);

		}

	 }
        catch(Exception $e){
                die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");
        }
}
?>


<html>
<head>
        <title> Indexing the Web Using Solr </title>
<style>
	body{
		background: gray; 
	}
	
</style>
</head>
<body>
<h1 align="center"> Comparing Search Engine Ranking Algorithms </h1><br/>
<form accept-charset="utf-8" method="get" align="center">

        Search: <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>"/><br/><br/> 
	<input type="radio" name="search" <?php if (isset($_GET['search']) && $_GET['search']=="lucene") echo 'checked="checked"';?>  value="lucene" /> Lucene(Default)
	<input type="radio" name="search" <?php if (isset($_GET['search']) && $_GET['search']=="pagerank") echo 'checked="checked"';?> value="pagerank" /> PageRank <br/><br/> 
	<input type="submit" />
</form>
<?php
if($results){
        $total = (int)$results->response->numFound; 
        $start = min(1,$total);
        $end = min($limit, $total); 
?>
<div> Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total;?>:</div> 
<ol style="list-style:none;">
<?php

foreach ($results->response->docs as $doc){
	 foreach($doc as $field => $value){
                if($field == "og_url" ){
                        $link = $value; 
                        $flag=1;
                }
                if($field == "id")
                {
                   $temp=$value; 
                }
        } 
?>

<li>

	<table style ="border: 1px solid black; text-align: left; border-radius:10px; ">

	<?php
		foreach($doc as $field => $value){
           
			if($field!="id" && $field!="title" && $field!="og_description" && $field!="og_url")continue;  
			if(sizeof($value)==1){ 
			?>
			<tr><th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') ; ?></th>
			<td><?php if(($field=="og_url" || $field=="title")&& $value!="")
            {?><a href = <?php echo $link ; ?>><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8')?></a> <?php }
                else echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8')?></td></tr>
			
			<?php } else {?>
			<tr><th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') ; ?></th>
			<td><ol>
			
				<?php 
					foreach($value as $item){
                
				?>

					<li><?php echo htmlspecialchars($item, ENT_NOQUOTES, 'utf-8' ); ?></li>

				<?php } ?>
			</ol></td></tr>
			<?php }} if($flag!=1){?><tr><th><?php echo htmlspecialchars("og_url", ENT_NOQUOTES, 'utf-8') ; ?></th>
			<td><a href = <?php echo $link ; ?>><?php echo htmlspecialchars($arr[$temp],  ENT_NOQUOTES, 'utf-8') ?></a></td></tr>
        
	<?php } ?>
		 

	</table></a></li>
	<?php $flag=0;} ?>
	</ol>
	<?php } ?>
</body>
</html>

