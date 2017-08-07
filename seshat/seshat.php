<?php
declare(strict_types=1);
class Seshat
{
    private $db = null;
    private $stemmer = null;

    function __construct(mysqli $db, string $prefix, Stemmer $stemmer)
    {
        $this->db = $db;
        $this->stemmer = $stemmer;
    }
    public function index(string $content, string $title, string $id) : bool
    {
        $content = $this->db->real_escape_string($this->uglify($content));
        $title = $this->db->real_escape_string($this->uglify($title));
        $externalId = $this->db->real_escape_string($id);
        $result = $this->db->query("SELECT `id` FROM `seshatFulltext` WHERE `external_id` = '$externalId'");
        if ($result && $result->num_rows != 0) {
            $id = $result->fetch_assoc()['id'];
            $this->db->query(
                "UPDATE `seshatFulltext` SET ".
                "`content` = '$content' ".
                "`title` = '$title' ".
                "WHERE `id` = $id"
            );
        }
        else {
            echo 'asd';
            $this->db->query(
                "INSERT INTO `seshatFulltext`".
                "(`id`, `external_id`, `title`, `content`) ". 
                "VALUES (NULL, '$externalId', '$title', '$content')"
            );
        }
        return true;
    }
    private function uglify(string $content) : string
    {
        $rendered = mb_strtolower($content);
        $rendered = strip_tags($rendered);
        $rendered = preg_replace("/[?\.,!\(\)\;\-]+/", '', $rendered);
        $rendered = preg_replace("/\s+(без|до|из|на|по|от|перед|при|через|нет|за|над|для|об|под|про)\s+/", '', $rendered);
        $rendered = str_replace('—', '', $rendered);
        $rendered = str_replace('–', '', $rendered);
        $rendered = str_replace("\n", ' ', $rendered);
        $words = explode(' ', $rendered);
        $resultArray = array();
        for ($i = 0; $i < count($words); $i++) {
            $word = $this->stemmer->getWordBase($words[$i]);
            if (mb_strlen($word) > 1 && !in_array($word, $resultArray, true)) {
                $resultArray[] = $word;
            }
        }
        return implode(' ', $resultArray);
    }
}
