<?php
global $protocol;
?>
					<h4>Integrate Follow URL in your Website</h4>
					<p>You can embed a map with your location in any website by including the following code in you HTML
					header.</p>
<code>&lt;style&gt;
	#follwMap {
		height: 250px;
	}
&lt;/style&gt;
&lt;link rel=&quot;stylesheet&quot; href=&quot;//unpkg.com/leaflet@1.7.1/dist/leaflet.css&quot;
	integrity=&quot;sha384-VzLXTJGPSyTLX6d96AxgkKvE/LRb7ECGyTxuwtpjHnVWVZs2gp5RDjeM/tgBnVdM&quot;
	crossorigin=&quot;anonymous&quot;/&gt;
&lt;script src=&quot;//unpkg.com/leaflet@1.7.1/dist/leaflet.js&quot;
	integrity=&quot;sha384-RFZC58YeKApoNsIbBxf4z6JJXmh+geBSgkCQXFyh+4tiFSJmJBt+2FbjxW7Ar16M&quot;
	crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;
&lt;script src=&quot;//<?= $_SERVER['HTTP_HOST'] ?>/follw.js&quot; crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;
&lt;script&gt;
	new Follw(&quot;follwMap&quot;, &quot;<?= $protocol . $_SERVER['HTTP_HOST'] ?>/followid&quot;, 12);
&lt;/script&gt;</code>
					<p>And include <code>&lt;div id=&quot;follwMap&quot;&gt;&lt;/div&gt;</code> wherever you want to show
					the map with your location.</p>