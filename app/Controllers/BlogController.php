<?php

require_once '../app/Models/Blog.php';


class BlogController {
    public function fetchPost(int $num): string {
        $blog = new Blog();
        $posts = $blog->fetch($num);
        $output = "";
        /* Render/Return table records as HTML for insertion into #content HTML element */
        foreach ($posts as $post){
            $output .= "<article>";
            $output .= "<h2>{$post['title']}</h2>";
            $output .= "<p>{$post['body']}</p>";
            $output .= "<hr>";
            $output .= "</article>";
        }

        return $output;

    }
}