<?php
/**
* Copyright (c) Microsoft Corporation.  All Rights Reserved.  Licensed under the MIT License.  See License in the project root for license information.
* 
* Schema File
* PHP version 7
*
* @category  Library
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
namespace Beta\Microsoft\Graph\ExternalConnectors\Model;

/**
* Schema class
*
* @category  Model
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
class Schema extends \Beta\Microsoft\Graph\Model\Entity
{
    /**
    * Gets the baseType
    *
    * @return string The baseType
    */
    public function getBaseType()
    {
        if (array_key_exists("baseType", $this->_propDict)) {
            return $this->_propDict["baseType"];
        } else {
            return null;
        }
    }
    
    /**
    * Sets the baseType
    *
    * @param string $val The baseType
    *
    * @return Schema
    */
    public function setBaseType($val)
    {
        $this->_propDict["baseType"] = $val;
        return $this;
    }
    

     /** 
     * Gets the schemaProperties
     *
     * @return array The schemaProperties
     */
    public function getSchemaProperties()
    {
        if (array_key_exists("properties", $this->_propDict)) {
           return $this->_propDict["properties"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the schemaProperties
    *
    * @param Property $val The schemaProperties
    *
    * @return Schema
    */
    public function setSchemaProperties($val)
    {
		$this->_propDict["properties"] = $val;
        return $this;
    }
    
}