<?	
$cssUrl = '/css/pqp.css';
$css = file_get_contents(Config::get('DocRoot').'/vendor/pqp/pqp.css');
?>
<style>
<?=$css?>
</style>
		
<!-- JavaScript -->
<script type="text/javascript">
	var PQP_DETAILS = false;
	var PQP_HEIGHT = "tall";
	
	//addEvent(window, 'load', loadCSS);
	
	var PQP_INPUT = {
		timer: null,
		clear: function(){
			PQP_INPUT.input = '';
			clearTimeout(PQP_INPUT.timer);
		},
		input: '',
		pattern: {
			show:'3838',
			hide:'4040'
		}
	};
	
	addEvent(window.document, 'keydown', function(e){
		PQP_INPUT.input += e ? e.keyCode : event.keyCode;
		PQP_INPUT.timer = setTimeout(PQP_INPUT.clear, 300);
		
		if (PQP_INPUT.input == PQP_INPUT.pattern.show)
		{
			PQP_INPUT.clear();
			document.getElementById('pqp-container').style.display = "block";
		}
		else if (PQP_INPUT.input == PQP_INPUT.pattern.hide)
		{
			PQP_INPUT.clear();
			document.getElementById('pqp-container').style.display = "none";
		}
		
	});

	function changeTab(tab) {
		var pQp = document.getElementById('pQp');
		hideAllTabs();
		addClassName(pQp, tab, true);
	}
	
	function hideAllTabs() {
		var pQp = document.getElementById('pQp');
		removeClassName(pQp, 'console');
		removeClassName(pQp, 'speed');
		removeClassName(pQp, 'queries');
		removeClassName(pQp, 'memory');
		removeClassName(pQp, 'files');
	}
	
	function toggleDetails(){
		var container = document.getElementById('pqp-container');
		
		if(PQP_DETAILS){
			addClassName(container, 'hideDetails', true);
			PQP_DETAILS = false;
		}
		else{
			removeClassName(container, 'hideDetails');
			PQP_DETAILS = true;
		}
	}
	function toggleHeight(){
		var container = document.getElementById('pqp-container');
		
		if(PQP_HEIGHT == "short"){
			addClassName(container, 'tallDetails', true);
			PQP_HEIGHT = "tall";
		}
		else{
			removeClassName(container, 'tallDetails');
			PQP_HEIGHT = "short";
		}
	}
	
	function loadCSS() {
		var sheet = document.createElement("link");
		sheet.setAttribute("rel", "stylesheet");
		sheet.setAttribute("type", "text/css");
		sheet.setAttribute("href", "$cssUrl");
		document.getElementsByTagName("head")[0].appendChild(sheet);
		setTimeout(function(){document.getElementById("pqp-container").style.display = "block"}, 10);
	}
	
	
	//http://www.bigbold.com/snippets/posts/show/2630
	function addClassName(objElement, strClass, blnMayAlreadyExist){
	   if ( objElement.className ){
	      var arrList = objElement.className.split(' ');
	      if ( blnMayAlreadyExist ){
	         var strClassUpper = strClass.toUpperCase();
	         for ( var i = 0; i < arrList.length; i++ ){
	            if ( arrList[i].toUpperCase() == strClassUpper ){
	               arrList.splice(i, 1);
	               i--;
	             }
	           }
	      }
	      arrList[arrList.length] = strClass;
	      objElement.className = arrList.join(' ');
	   }
	   else{  
	      objElement.className = strClass;
	      }
	}

	//http://www.bigbold.com/snippets/posts/show/2630
	function removeClassName(objElement, strClass){
	   if ( objElement.className ){
	      var arrList = objElement.className.split(' ');
	      var strClassUpper = strClass.toUpperCase();
	      for ( var i = 0; i < arrList.length; i++ ){
	         if ( arrList[i].toUpperCase() == strClassUpper ){
	            arrList.splice(i, 1);
	            i--;
	         }
	      }
	      objElement.className = arrList.join(' ');
	   }
	}

	//http://ejohn.org/projects/flexible-javascript-events/
	function addEvent( obj, type, fn ) {
	  if ( obj.attachEvent ) {
	    obj["e"+type+fn] = fn;
	    obj[type+fn] = function() { obj["e"+type+fn]( window.event ) };
	    obj.attachEvent( "on"+type, obj[type+fn] );
	  } 
	  else{
	    obj.addEventListener( type, fn, false );	
	  }
	}
</script>

<?$hide = (Config::isLive()) ? ' style="display:none"' : '';?>

<div id="pqp-container" class="pQp tallDetails hideDetails"<?=$hide?>>
<?
$logCount = count($output['logs']['console']);
$fileCount = count($output['files']);
$memoryUsed = $output['memoryTotals']['used'];
$queryCount = $output['queryTotals']['count'];
$speedTotal = $output['speedTotals']['total'];
?>

<div id="pQp" class="console">
<table id="pqp-metrics" cellspacing="0">
<tr>
	<td class="green" onclick="changeTab('console');">
		<var><?=$logCount?></var>
		<h4>Console</h4>
	</td>
	<td class="blue" onclick="changeTab('speed');">
		<var><?=$speedTotal?></var>
		<h4>Load Time</h4>
	</td>
	<td class="purple" onclick="changeTab('queries');">
		<var><?=$queryCount?> Queries</var>
		<h4>Database</h4>
	</td>
	<td class="orange" onclick="changeTab('memory');">
		<var><?=$memoryUsed?></var>
		<h4>Memory Used</h4>
	</td>
	<td class="red" onclick="changeTab('files');">
		<var><?=$fileCount?> Files</var>
		<h4>Included</h4>
	</td>
</tr>
</table>
<div id="pqp-console" class="pqp-box">
<?php if($logCount ==  0) : ?>
	<h3>This panel has no log items.</h3>
<?php else: ?>
		<table class="side" cellspacing="0">
			<tr>
				<td class="alt1"><var><?=$output['logs']['logCount']?></var><h4>Logs</h4></td>
				<td class="alt2"><var><?=$output['logs']['errorCount']?></var> <h4>Errors</h4></td>
			</tr>
			<tr>
				<td class="alt3"><var><?=$output['logs']['memoryCount']?></var> <h4>Memory</h4></td>
				<td class="alt4"><var><?=$output['logs']['speedCount']?></var> <h4>Speed</h4></td>
			</tr>
		</table>
		<table class="main" cellspacing="0">
		
		<?php $class = '';?>
		<?php foreach($output['logs']['console'] as $log) : ?>
			<tr class="log-<?=$log['type']?>">
				<td class="type"><?=$log['type']?></td>
				<td class="<?=$class?>">
			<?php if($log['type'] == 'log') :?>
				<div>
					<pre><?=$log['data']?></pre>
				</div>
			<?php elseif($log['type'] == 'memory') :?>
				<div>
					<pre><?=$log['data']?></pre> <em><?=$log['dataType']?></em>: <?=$log['name']?> </div>
			<?php elseif($log['type'] == 'speed') :?>
				<div><pre><?=$log['data']?></pre> <em><?=$log['name']?></em></div>
			<?php elseif($log['type'] == 'error') :?>
				<div><em>Line <?=$log['line']?></em> : <?=$log['data']?> <pre><?=$log['file']?></pre></div>
			<?php endif?>
		
			</td></tr>
			<?php $class = ($class == '') ? 'alt' : ''?>
		<?php endforeach?>
		</table>
<?php endif?>

</div>

<div id="pqp-speed" class="pqp-box">

<?php if($output['logs']['speedCount'] ==  0) : ?>
	<h3>This panel has no log items.</h3>
<?php else:?>
		<table class="side" cellspacing="0">
			<tr>
				<td><var><?=$output['speedTotals']['total']?></var><h4>Load Time</h4></td>
			</tr>
		  	<tr>
		  		<td class="alt"><var><?=$output['speedTotals']['allowed']?></var> <h4>Max Execution Time</h4></td>
		  	</tr>
		</table>
		<table class="main" cellspacing="0">
		
		<?$class = '';?>
		<?php foreach($output['logs']['console'] as $log) :?>
			<?php if($log['type'] == 'speed') :?>
				<tr class="log-<?=$log['type']?>">
				<td class="<?=$class?>">
					<div><pre><?=$log['data']?></pre> <em><?=$log['name']?></em></div>
				</td></tr>
				<?php $class = ($class == '') ? 'alt' : ''?>
			<?php endif?>
		<?php endforeach?>
		</table>
<?php endif?>

</div>

<div id="pqp-queries" class="pqp-box">

<?php if($output['queryTotals']['count'] ==  0) :?>
	<h3>This panel has no log items.</h3>
<?php else: ?>
		<table class="side" cellspacing="0">
		  	<tr>
		  		<td><var><?=$output['queryTotals']['count']?></var><h4>Total Queries</h4></td>
		  	</tr>
		  	<tr>
		  		<td class="alt"><var><?=$output['queryTotals']['time']?></var> <h4>Total Time</h4></td>
		  	</tr>
		  	<tr><td><var>0</var> <h4>Duplicates</h4></td></tr>
		</table>
		<table class="main" cellspacing="0">';
		
		<?php $class = '';?>
		<?php foreach($output['queries'] as $query) :?>
			<tr>
				<td class="<?=$class?>">
					<?=$query['sql']?>
			<?php if($query['explain']) :?>
					<em>
					Possible keys: <b><?=$query['explain']['possible_keys']?></b> &middot; 
					Key Used: <b><?=$query['explain']['key']?></b> &middot; 
					Type: <b><?=$query['explain']['type']?></b> &middot; 
					Rows: <b><?=$query['explain']['rows']?></b> &middot; 
					Speed: <b><?=$query['time']?></b> &middot;
					Database: <b><?=$query['database']?></b>
					</em>
			<?php else :?>
				<?php if ($query['raw_time'] > 1000)
					$speed_class = "pqp-slowquery-high";
				elseif ($query['raw_time'] > 100)
					$speed_class = "pqp-slowquery-mid";
				else
					$speed_class = "pqp-slowquery-low";
				?>
					<em>
					Speed: <b><span class="'.$speed_class.'"><?=$query['time']?></span></b> &middot;
					Database: <b><?=$query['database']?></b>
					</em>
			<?php endif?>
				</td>
			</tr>
			<?php ($class == '') ? 'alt' : $class = ''?>
		<?php endforeach?>	
		</table>
<?php endif?>

</div>

<div id="pqp-memory" class="pqp-box">

<?php if($output['logs']['memoryCount'] ==  0) :?>
	<h3>This panel has no log items.</h3>
<?php else: ?>
		<table class="side" cellspacing="0">
		  <tr><td><var><?=$output['memoryTotals']['used']?></var><h4>Used Memory</h4></td></tr>
		  <tr><td class="alt"><var><?=$output['memoryTotals']['total']?></var> <h4>Total Available</h4></td></tr>
		 </table>
		<table class="main" cellspacing="0">';
		
		<?$class = '';?>
		<?php foreach($output['logs']['console'] as $log) : ?>
			<?php if($log['type'] == 'memory') : ?>
				<tr class="log-<?=$log['type']?>">
					<td class="<?=$class?>"><b><?=$log['data']?></b> <em><?=$log['dataType']?></em>: <?=$log['name']?></td>
				</tr>
				<?php ($class == '') ? 'alt' : $class = ''?>
			<?php endif?>
		<?php endforeach?>
			
		</table>
<?php endif?>

</div>

<div id="pqp-files" class="pqp-box">

<?php if($output['fileTotals']['count'] ==  0) : ?>
	<h3>This panel has no log items.</h3>
<?php else:?>
	<table class="side" cellspacing="0">
		  	<tr><td><var><?=$output['fileTotals']['count']?></var><h4>Total Files</h4></td></tr>
			<tr><td class="alt"><var><?=$output['fileTotals']['size']?></var> <h4>Total Size</h4></td></tr>
			<tr><td><var><?=$output['fileTotals']['largest']?></var> <h4>Largest</h4></td></tr>
			<tr><td><var><?=date("Y-m-d H:i:s",$output["fileTotals"]["newest"])?></var> <h4>Newest</h4></td></tr>
		 </table>
		<table class="main" cellspacing="0">';
		
		<?$class ='';?>
		<?php foreach($output['files'] as $file) : ?>
			<tr><td class="<?=$class?>"><b><?=$file['size']?></b><em><?=date("Y-m-d H:i:s",$file["age"])?></em> <?=$file['name']?> </td></tr>
			<?php $class = ($class == '') ? 'alt' : '';?>
		<?php endforeach?>
			
		</table>
<?php endif?>

</div>

	<table id="pqp-footer" cellspacing="0">
		<tr>
			<td class="credit">
				<a href="http://particletree.com" target="_blank">
				<strong>PHP</strong> 
				<b class="green">Q</b><b class="blue">u</b><b class="purple">i</b><b class="orange">c</b><b class="red">k</b>
				Profiler</a></td>
			<td class="actions">
				<a href="#" onclick="toggleDetails();return false;">+/-</a>
			</td>
		</tr>
	</table>
		
</div>
</div>


