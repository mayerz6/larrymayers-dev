<?php

class Blog {

    private static $title = "Blank";
    private static $author = "Blank";
    private static $contents = "Blank";

    public static function setBlogEntry($entryTitle, $entryAuthor, $entryContents) : void { 
        self::$title = $entryTitle;
        self::$author = $entryAuthor;
        self::$contents = $entryContents;

        $path = "../../blog_entries.csv";
        $fp = fopen($path, 'a');
        fputcsv($fp, [1, $entryTitle, $entryAuthor, $entryContents, date('m/d/Y')]);
        fclose($fp);
        /* Store/Insert provided contents within requisite data store. */

     }

     public static function displayBlogEntry(){
        echo self::$title . " " . self::$author;
     }
}