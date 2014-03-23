<?php
class Lang
{
  /**
   * setLocale(<string> Language)
   * getLocale(void)
   * getLangFiles(void)
   * get(<string> file.name[, <array> args])
   * choice(<string> file.name[, <int> choice [, <array> args]])
   */

  private static $lang = false;
  
  public static function setLocale($language)
  {
    if(!file_exists('./language/'.$language))
      ERROR::generate(404, "Locale not found!");
    else
    {
      $time = 60 * 60 * 24 * 31; # Session time
      COOKIE::set('lang', $language, $time);
      self::$lang = $language;
    }
  }
  
  public static function getLocale()
  {
    $lang = COOKIE::get('lang');
    if($lang !== false)
    {
      if(file_exists('./language/'.$lang))
      {
        self::$lang = $lang;
      }
    }
    return self::$lang;
  }

  public static function setDefault($language)
  {
    if(file_exists('./language/'.$language))
      self::$lang = $language;
    else
      ERROR::generate(404, "Locale not found!");
  }

  public static function getLangFiles()
  {
    $ret = array();
    if($handle = opendir('./language/'))
    {
      while (false !== ($entry = readdir($handle)))
      {
        if($entry != "." and $entry != "..")
          $ret[] = $entry;
      }
    }
    return $ret;
  }
  
  private static function getLangFile($str)
  {
    if(self::$lang !== false)
    {
      $exp = explode('.', $str);
      if(count($exp) == 2)
      {
        $inc = './language/'.self::$lang.'/'.$exp[0].'.php';
        if(file_exists($inc))
        {
          return include($inc);
        }
        else
          ERROR::generate(404, "Language file does not exist.");
      }
      else
        ERROR::generate(404, "Could not find folder/file.");
    }
    else
      dd(self::$lang);
    //  ERROR::generate(400, "(Default) Locale not set.");
    return false;
  }
  
  private static function replaceArgs($str, $args = array())
  {
    if(strpos($str, ':') !== false)
    {
      $expl = explode(':', $str);
      for($i = 0; $i < count($expl); $i++)
      {
        if(array_key_exists($expl[$i], $args))
        {
          $expl[$i] = $args[$expl[$i]];
        }
      }
      return implode('', $expl);
    }
    else
      return $str;
  }
  
  public static function get($str, $args = array())
  {
    if($lang = self::getLangFile($str))
    {
      $exp = explode('.', $str);
      $return = self::replaceArgs($lang[$exp[1]], $args);
      
      return (empty($return)) ? $exp[1] : $return;
    }
    else
      return $str;
  }
  
  public static function choice($str, $choice = 0, $args = array())
  {
    if($lang = self::getLangFile($str))
    {
      $exp = explode('.', $str);
      
      if(empty($lang[$exp[1]]))
        return $exp[1];
      else
      {
        $str = $lang[$exp[1]];
        if(strpos($str, '|') !== false)
        {
          $expChoice = explode('|', $str);
          $return = self::replaceArgs($expChoice[$choice], $args);
          
          if(empty($return))
            $return = $expChoice[count($expChoice)-1];
          
          return $return;
        }
        else
          return $str;
      }
    }
  }
}