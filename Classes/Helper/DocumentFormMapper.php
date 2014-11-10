<?php
namespace EWW\Dpf\Helper;

class DocumentFormMapper {
  
  protected $form = array();
  
  protected $document;
  
  protected $xmlData;
  
    
  public function setDocument($document) {    
    $this->document = $document;    
    $this->xmlData = new \SimpleXMLElement($document->getXmlData());
  }      
  
  public function getDocumentForm($node) {

    $form['config']['uid'] = $node->getUid();
    $form['config']['displayName'] = $node->getDisplayName();
    $form['pages'] = $this->createDocumentForm($node);
       
    return $form;
  }
  
  protected function createDocumentForm($node, \SimpleXMLElement $xmlData = NULL) {
      
    foreach ($node->getChildren() as $child) {

      $item = array();
      $field = array();

      switch (get_class($child)) {

        case 'EWW\Dpf\Domain\Model\MetadataGroup':
          $item['uid'] = $child->getUid();
          $item['displayName'] = $child->getDisplayName();
          $item['mandatory'] = $child->getMandatory();

          // Read the group data.
          $xmlData = $this->xmlData->xpath($child->getMapping());

          if ($xmlData) {
            foreach ($xmlData as $key => $data) {
             $item['items'][$key]['fields'] = $this->createDocumentForm($child, $data);
            }
          } else {
            $item['items'][0]['fields'] = $this->createDocumentForm($child);
          }
          break;

        case 'EWW\Dpf\Domain\Model\MetadataObject':         
          $item['uid'] = $child->getUid();
          $item['displayName'] = $child->getDisplayName();
          $item['mandatory'] = $child->getMandatory();
          $item['inputField'] = $child->getInputField();

          $objectMapping = $child->getMapping();
          $objectMapping = trim($objectMapping,'/');                    

          if ($xmlData) {
            $objectXml = $xmlData->xpath($objectMapping);
            if ($objectXml) {
              foreach ($objectXml as $key => $value) {
                $item['items'][] = (string)$value;
              }
            } else {
              $item['items'][] = NULL;
            }
          } else {
            $item['items'][] = NULL;
          }     
          break;

        default:
          $item['config']['uid'] = $child->getUid();
          $item['config']['displayName'] = $child->getDisplayName();
          $item['groups'] = $this->createDocumentForm($child, $xmlData);
          break;
      }
     
      $form[] = $item;                          
    }                     
    
    return $form;              
  }                 
  
}

?>