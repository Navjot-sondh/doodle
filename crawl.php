<?php
include("classes/DomDocumentParser.php");

$alreadyCrawled=array();
$crawling=array();
function followLink($url)
{
    global $alreadyCrawled;
    global $crawling;
    $parser=new DomDocumentParser($url);
    echo $url."<br>";
    $linkList=$parser->getLinks();
    foreach($linkList as $link)
    {
        $href=$link->getAttribute("href");
        if(strpos($href,"#")!==false)
        {
            continue;
        }
        else if(substr($href,0,11)=="javascript")
        {
            continue;
        }
        $href=createLink($href,$url);
        if(!in_array($href,$alreadyCrawled))
        {
            $alreadyCrawled[]=$href;
            $crawling[]=$href;
            getDetails($href);
        }
        array_shift($crawling);
        foreach($crawling as $site)
        {
            followLinks($site);
        }

        echo $href."<br>";
    }
    array_shift($crawling);
    foreach($crawling as $site)
    {
        followLinks($site);
    }

}//End of function followLinks()
function getDetails($url)
{
    $parser= new DomDocumentParser($url);
    $titleArray=$parser->getTitleTags();
    if(sizeof($titleArray)==0 || $titleArray->item(0)==NULL)
    {
        return;
    }
    $title=$titleArray->item(0)->nodeValue;
    $title=str_replace("\n","",$title);
    if($title=="")
    {
        return;
    }
    echo "URL:$url,Title:$title<br>";
}
function createLink($src,$url)
{
    $scheme=parse_url($url)["scheme"];
    $host=parse_url($url)["host"];
    if(substr($src,0,2)=="//")
    {
        $src=parse_url($url)["scheme"].":".$src;
    }
    else if(substr($src,0,1)=="/")
    {
        $src=$scheme.":".$host.$src;
    }

    else if(substr($src,0,2)=="./")
    {
        $src=$scheme."://".$host.dirname(parse_url($url)["path"]).substr($src,1);
    }
    else if(substr($src,0,3)=="../")
    {
        $src=$scheme."://".$host."/".$src;
    }
    else if(substr($src,0,5)!="https" && substr($src,0,4)!="http")
    {
        $src=$scheme."://".$host."/".$src;
    }
    return $src;
}

$startUrl="http://www.bbc.com";
followLink($startUrl);
?>
