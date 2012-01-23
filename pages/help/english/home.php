<p>
	It looks like you're accessing help for the home page.
    We don't have a lot of information here, however we
    can tell you where to find this file if you want to
    rewrite it to be your own help page. 
</p>

<hr class="space">

<pre><?php echo "/page/help/" . strtolower($this->is_language()) . "/" . $this->q("slug") . ".php";?></pre>