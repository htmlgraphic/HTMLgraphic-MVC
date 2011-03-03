<?php
class RSS
{
	static function insert_rss_category_tooltip(Category $category, $id)
	{
		$rss_link = ($category->subcategory == '') ? $category->category . '.xml' : $category->category . ':' . $category->subcategory . '.xml';

		$rss_link = "http://feeds./category/" . $rss_link;

		self::insert_rss_tooltip_with_rss_link($rss_link, $id);
	}

	static function insert_category_rss_div(Category $category, $id)
	{
		$rss_link = ($category->subcategory == '') ? $category->category . '.xml' : $category->category . ':' . $category->subcategory . '.xml';

		$rss_link = "http://feeds./category/" . $rss_link;

		self::insert_rss_div_with_rss_link($rss_link, $id);
	}

	static function insert_author_rss_div(Author $author, $id)
	{

		$rss_link = self::getRSSFeedForAuthor($author);

		self::insert_rss_div_with_rss_link($rss_link, $id);
	}

	static function getRSSFeedForCategory(Category $category)
	{
		$rss_link = ($category->subcategory == '') ? $category->category . '.xml' : $category->category . ':' . $category->subcategory . '.xml';

		return "http://feeds./category/" . $rss_link;
	}

	static function getRSSFeedForAuthor(Author $author)
	{
		return "http://feeds./expert/{$author->getURLEncodedName()}.xml";
	}

	private static function insert_rss_tooltip_with_rss_link($rss_link, $id)
	{
?>
		<div id="rss-tooltip-<?= $id ?>" style="background-color:#fffecd;display:none;position:absolute;text-align:center;padding-top:10px;width:225px;border:1px solid #000000;">
			<p>
				<a href="#" id="close-rss" title="Close This Form">
					<span style="float:right;margin-right:8px;">
						<img src="http://img./spriting/trans.gif" class="sprite s_close">
					</span>
				</a>
				<img src="http://img./spriting/trans.gif" style="margin-left:16px;" class="sprite s_rsstitle">
			</p>

			<p>
				<a href="http:///rss_what/">
					<img src="http://img./spriting/trans.gif" class="sprite s_rssinfo">
				</a>
			</p>
			<p>
				<a href="<?= $rss_link ?>">
					<img src="http://img./spriting/trans.gif" class="sprite s_xml">
				</a>
				<br>
			</p>

			<p>
<?
		$sites = self::getSites($rss_link);
		foreach ($sites as $site => $info)
		{
?><a target="_new" href="<?= $info["link"] ?>"><img src="http://img./spriting/trans.gif" class="sprite <?= $info["image_sprite"] ?>"></a><?
		}
?>
	</p>
</div>




<?
	}

	public static function insertRSSLinksForCategory(Category $category)
	{
		$rss_link = self::getRSSFeedForCategory($category);

		$sites = self::getSites($rss_link);
		foreach ($sites as $site => $info)
		{
?>
			<a target="_new" href="<?= $info["link"] ?>"><img src="http://img./spriting/trans.gif" class="sprite <?= $info["image_sprite"] ?>" alt="<?= $info["alt"] ?>"></a>
<?
		}
	}

	private static function insert_rss_div_with_rss_link($rss_link, $id)
	{
		//$sites = self::getSites($rss_link);
?>
		<a href="<?= $rss_link ?>" class="info" onclick="return false;">
			<img src="http://img./spriting/trans.gif" class="sprite s_rssicon" id="rss-icon-link-<?= $id ?>" alt="" border="0" style="position:relative;top:4px">
		</a>

		<span class="toolTip" id="rss-tooltip-<?= $id ?>" style="display:none;"></span>
<?
	}

	private static function getSites($rss_link)
	{
		return array(
		    "Google" => array("link" => "http://fusion.google.com/add?feedurl=$rss_link", "image" => "/images/addgoogle.gif", "image_sprite" => "s_google", "alt" => "Add To Google"),
		    "Yahoo" => array("link" => "http://add.my.yahoo.com/rss?url=$rss_link", "image" => "/images/myyahoo.gif", "image_sprite" => "s_yahoo", "alt" => "Add to My Yahoo"),
		    "NewsGator" => array("link" => "http://www.newsgator.com/ngs/subscriber/subext.aspx?url=$rss_link", "image" => "/images/mynewsgator.gif", "image_sprite" => "s_newsgator", "alt" => "Add To Newsgator"),
		    "Bloglines" => array("link" => "http://www.bloglines.com/sub/$rss_link", "image" => "/images/bloglines.gif", "image_sprite" => "s_bloglines", "alt" => "Add to Sub Bloglines"),
		    "MSN" => array("link" => "http://my.msn.com/addtomymsn.armx?id=rss&amp;ut=$rss_link", "image" => "/images/msn-icon.gif", "image_sprite" => "s_msn", "alt" => "Add To MyMSN"),
		    "AOL" => array("link" => "http://feeds.my.aol.com/add.jsp?url=$rss_link", "image" => "/images/myaol.gif", "image_sprite" => "s_aol", "alt" => "Add To My AOL")
		);
	}

}
?>
