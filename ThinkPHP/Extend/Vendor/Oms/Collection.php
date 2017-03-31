<?php
/** 
 * Generic Collection class 
 * 
 * Based on: http://codeutopia.net/code/library/CU/Collection.php 
 * 
 * My additions are marked with "--aaron" 
 */  
class CU_Collection implements Countable, IteratorAggregate, ArrayAccess  
{  
    protected $_valueType;  
  
    // --aaron  
    /** 
     * Whether or not the members or this collection are of a "basic" type 
     * 
     * Basic types are anything that has a matching "is_*" function listed 
     * in {@link http://us2.php.net/manual/en/ref.var.php}. Anything else is 
     * assumed to be the name of a class or interface. 
     * 
     * @var boolean 
     */  
    protected $_isBasicType = false;  
  
    // --aaron  
    /** 
     * The "is_*" function that validates {@link $_valueType} 
     * 
     * This property is only used if {@link $_isBasicType} == true 
     * @var string 
     */  
    protected $_validateFunc;  
  
    protected $_collection = array();  
  
    /** 
     * Construct a new typed collection 
     * @param string valueType collection value type 
     */  
    public function __construct($valueType)  
    {
        $this->_valueType = $valueType;
        // --aaron
        if(function_exists("is_$valueType")) {  
            $this->_isBasicType = true;
            $this->_validateFunc = "is_$valueType";
        }
    }
  
    /** 
     * Add a value into the collection 
     * @param string $value 
     * @throws InvalidArgumentException when wrong type 
     */  
    public function add($value)  
    {  
        if(!$this->isValidType($value))  
            throw new InvalidArgumentException('Trying to add a value of wrong type');  
  
        $this->_collection[] = $value;  
    }  
  
    /** 
     * Set index's value 
     * @param integer $index 
     * @param mixed $value 
     * @throws OutOfRangeException 
     * @throws InvalidArgumentException 
     */  
    public function set($index, $value)  
    {  
        if($index >= $this->count())  
            throw new OutOfRangeException('Index out of range');  
  
        if(!$this->isValidType($value))  
            throw new InvalidArgumentException('Trying to add a value of wrong type');  
  
        $this->_collection[$index] = $value;  
    }  
  
    /** 
     * Remove a value from the collection 
     * @param integer $index index to remove 
     * @throws OutOfRangeException if index is out of range 
     */  
    public function remove($index)  
    {  
        if($index >= $this->count())  
            throw new OutOfRangeException('Index out of range');  
  
        array_splice($this->_collection, $index, 1);  
    }  
  
    /** 
     * Return value at index 
     * @param integer $index 
     * @return mixed 
     * @throws OutOfRangeException 
     */  
    public function get($index)  
    {  
        if($index >= $this->count())  
            throw new OutOfRangeException('Index out of range');  
  
        return $this->_collection[$index];  
    }  
  
    /** 
     * Determine if index exists 
     * @param integer $index 
     * @return boolean 
     */  
    public function exists($index)  
    {  
        if($index >= $this->count())  
            return false;  
  
        return true;  
    }  
    /** 
     * Return count of items in collection 
     * Implements countable 
     * @return integer 
     */  
    public function count()  
    {  
        return count($this->_collection);  
    }  
  
    /** 
     * Determine if this value can be added to this collection 
     * @param string $value 
     * @return boolean 
     */  
    public function isValidType($value)  
    {  
        // --aaron  
        if($this->_isBasicType) {  
            $validateFunc = $this->_validateFunc;  
            return $validateFunc($value);  
        } else {  
            // instanceof works on interfaces as well as classes.  
            // It also checks the entire inheritance chain
			return $value instanceof $this->_valueType;
        }  
    }  
  
    /** 
     * Return an iterator 
     * Implements IteratorAggregate 
     * @return ArrayIterator 
     */  
    public function getIterator()  
    {  
        return new ArrayIterator($this->_collection);  
    }  
  
    /** 
     * Set offset to value 
     * Implements ArrayAccess 
     * @see set 
     * @param integer $offset 
     * @param mixed $value 
     */  
    public function offsetSet($offset, $value)  
    {  
        $this->set($offset, $value);  
    }  
  
    /** 
     * Unset offset 
     * Implements ArrayAccess 
     * @see remove 
     * @param integer $offset 
     */  
    public function offsetUnset($offset)  
    {  
        $this->remove($offset);  
    }  
  
    /** 
     * get an offset's value 
     * Implements ArrayAccess 
     * @see get 
     * @param integer $offset 
     * @return mixed 
     */  
    public function offsetGet($offset)  
    {  
        return $this->get($offset);  
    }  
  
    /** 
     * Determine if offset exists 
     * Implements ArrayAccess 
     * @see exists 
     * @param integer $offset 
     * @return boolean 
     */  
    public function offsetExists($offset)  
    {  
        return $this->exists($offset);  
    }  
}