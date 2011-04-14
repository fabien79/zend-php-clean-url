<?php
class Generator {

    /**
     * An instance of Zend_Db_Adapter.
     */
    protected $_db;

    /**
     * You should initialize $_db here. The example is if you had an _init() 
     * method for Zend_Registry::set('dbAdapter') in your Bootstrap.php file.
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        //$this->_db = Zend_Registry::get('dbAdapter');
    }
    
    /**
     * Generates a uristub of maximum specified length.
     *
     * @access  protected
     * @param   string  $tableName      The name of the table to query
     * @param   string  $tableField     The table column to check
     * @param   string  $uristub        The initial input string (page title) to clean
     * @param   int     $length         The maximum allowable length for the clean url
     * @param   mixed   $iteration      The current iteration, when duplicates found
     * @return  string
     */
    protected function generateUristub($tableName, $tableField, $uristub, $length = 30, $iteration = NULL)
    {
        // begin uristub generation on first iteration
        if (is_null($iteration)) {
            // set the locale, just once
            setlocale(LC_ALL, 'en_US.UTF8');
            // clean the uristub
            $uristub = iconv('UTF-8', 'ASCII//TRANSLIT', $uristub);
            $uristub = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $uristub);
            $uristub = preg_replace("/[\/_|+ -]+/", '-', $uristub);
            $uristub = strtolower(trim($uristub, '-'));
    
            // ensure uristub is less than length
            if (strlen($uristub) > $length) {
                // get char at chopped position
                $char = $uristub[$length-1];
                // quick chop (leave room for 9 iterations)
                $uristub = substr($uristub, 0, $length - 1);
    
                // if we chopped mid word
                if ($char != '-') {
                        $pos = strrpos($uristub, '-');
                        if ($pos !== FALSE) {
                                $uristub = substr($uristub, 0, $pos);
                        }
                }
            }
        }
        
        // if we have an iteration, add to the uristub
        $uristubToCheck = !empty($iteration) ? $uristub . $iteration : $uristub;
        
        // check if the uristub exists
        $sql = sprintf('SELECT 1 FROM `%s` WHERE `%s` = %s',
                       $tableName,
                       $tableField,
                       $this->_db->quote($uristubToCheck));
        
        try {
            $result = $this->_db->query($sql)->fetch();
            if (!$result) {
                return $uristubToCheck;
            }
        } catch (Exception $e) {
            // fatal
            die($e->getMessage());
        }
        
        // increment iteration before trying again
        $iteration = (is_null($iteration)) ? 1 : ++$iteration;
        return $this->generateUristub($tableName, $tableField, $uristub, $length, $iteration);
    }

}
