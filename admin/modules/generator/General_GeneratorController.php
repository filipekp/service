<?php
  namespace prosys\admin\controller;

use DateTime,
  DOMDocument,
  DOMElement,
  DOMXPath,
  prosys\core\common\Agents,
  prosys\core\common\AppException,
  prosys\core\common\Functions,
  prosys\model\PartnerAccessDao,
  prosys\model\PartnerAccessEntity,
  prosys\model\PartnerDao,
  prosys\model\PartnerEntity,
  prosys\model\PartnerProducerEntity,
  prosys\model\ProductDao,
  prosys\model\ProductEntity,
  prosys\model\SKCZ_CategoryDao;
use prosys\core\common\types\Zip;
use prosys\core\mapper\SqlFilter;

/**
   * Processes the users requests.
   * 
   * // testovaci Schau output CSV: service.styleplus.cz/get-products.php?identification=fWn8k4JoWKnRmfYM&output_type=csv
   * 
   * @author Pavel Filípek <www.filipek-czech.cz>
   * @copyright (c) 2017, Proclient s.r.o.
   */
  abstract class General_GeneratorController extends Controller
  {
    const REQUEST_TYPE_GET  = 'get';
    const REQUEST_TYPE_POST = 'post';
    
    const CACHE_TIME = '3 hour'; 
    const CACHE_DELETE_TIME = '7 days';
    
    const MIN_PROFIT_DEFAULT = 0;
    
    /** @var PartnerDao */
    private $partnerDao = NULL;
    /** @var SKCZ_CategoryDao */
    private $categoryDao = NULL;
    /** @var ProductDao */
    private $productDao = NULL;
    
    private $dir = NULL;
    private $fileName = NULL;
    private $filePath = NULL;
    private $requestType = self::REQUEST_TYPE_POST;
    /** @var boolean */
    private $isAdmin = FALSE;
    
    private $statusMessage;
    private $statusCode;
    private $countItems;
    
    const COLUMN_CATEGORY             = 1;
    const COLUMN_PRODUCER             = 2;
    const COLUMN_CATALOG              = 3;
    const COLUMN_EAN                  = 4;
    const COLUMN_NAME                 = 5;
    const COLUMN_DESCRIPTION          = 6;
    const COLUMN_RETAIL_PRICE         = 7;
    const COLUMN_PRICE                = 8;
    const COLUMN_PRICE_OK             = 9;
    const COLUMN_GUARANTEE            = 10;
    const COLUMN_STOCK                = 11;
    const COLUMN_DELIVERY             = 12;
    const COLUMN_ESHOP_PRICE          = 13;
    const COLUMN_MODIFIED_AT          = 14;
    const COLUMN_SERIE                = 15;
    const COLUMN_PRODUCT_NAME         = 16;
    const COLUMN_REMOVED              = 17;
    const COLUMN_PRICE_FOREIGN        = 18;
    const COLUMN_RETAIL_PRICE_FOREIGN = 19;
    const COLUMN_STOCK_COUNT          = 20;
    const COLUMN_WEIGHT               = 21;
    const COLUMN_ID                   = 22;
    const COLUMN_SELLOUT              = 23;
    
    protected $allColumnsDefinition = [
      self::COLUMN_CATEGORY             => ['column' => 'Category', 'heading' => ['cz' => 'Kategorie', 'sk' => 'Kategória']],
      self::COLUMN_PRODUCER             => ['column' => 'Producer', 'heading' => ['cz' => 'Výrobce', 'sk' => 'Výrobca']],
      self::COLUMN_CATALOG              => ['column' => 'Catalog', 'heading' => ['cz' => 'Katalogové číslo', 'sk' => 'Katalógové číslo']],
      self::COLUMN_EAN                  => ['column' => 'EAN', 'heading' => ['cz' => 'EAN', 'sk' => 'EAN']],
      self::COLUMN_NAME                 => ['column' => 'Name', 'heading' => ['cz' => 'Název', 'sk' => 'Názov']],
      self::COLUMN_DESCRIPTION          => ['column' => 'Description', 'heading' => ['cz' => 'Popis', 'sk' => 'Popis']],
      self::COLUMN_RETAIL_PRICE         => ['column' => 'RetailPrice', 'heading' => ['cz' => 'MOC', 'sk' => 'MOC']],
      self::COLUMN_PRICE                => ['column' => 'Price', 'heading' => ['cz' => 'Vaše nákupní cena', 'sk' => 'Vaša nákupná cena']],
      self::COLUMN_PRICE_OK             => ['column' => 'PriceOK', 'heading' => ['cz' => 'Doporučená prodejní cena', 'sk' => 'Odporúčaná predajná cena']],
      self::COLUMN_GUARANTEE            => ['column' => 'Guarantee', 'heading' => ['cz' => 'Záruka', 'sk' => 'Záruka']],
      self::COLUMN_STOCK                => ['column' => 'Stock', 'heading' => ['cz' => 'Skladem', 'sk' => 'Skladom']],
      self::COLUMN_DELIVERY             => ['column' => 'Delivery', 'heading' => ['cz' => 'Dodací lhůta', 'sk' => 'Dodacia lehota']],
      self::COLUMN_ESHOP_PRICE          => ['column' => 'EshopPrice', 'heading' => ['cz' => 'Doporučená cena', 'sk' => 'Odporúčaná cena']],
      self::COLUMN_MODIFIED_AT          => ['column' => 'ModifiedAt', 'heading' => ['cz' => 'Změněno', 'sk' => 'Zmenené']],
      self::COLUMN_SERIE                => ['column' => 'Serie', 'heading' => ['cz' => 'Série', 'sk' => 'Séria']],
      self::COLUMN_PRODUCT_NAME         => ['column' => 'ProductName', 'heading' => ['cz' => 'Název', 'sk' => 'Názov']],
      self::COLUMN_REMOVED              => ['column' => 'Removed', 'heading' => ['cz' => 'Neaktivní', 'sk' => 'Neaktívny']],
      self::COLUMN_PRICE_FOREIGN        => ['column' => 'PriceForeign', 'heading' => ['cz' => 'Cena v cizí měně', 'sk' => 'Cena v cudzej mene']],
      self::COLUMN_RETAIL_PRICE_FOREIGN => ['column' => 'RetailPriceForeign', 'heading' => ['cz' => 'MOC v cizí měně', 'sk' => 'MOC v cudzej mene']],
      self::COLUMN_STOCK_COUNT          => ['column' => 'StockCount', 'heading' => ['cz' => 'Kusů skladem', 'sk' => 'Kusov skladom']],
      self::COLUMN_WEIGHT               => ['column' => 'Weight', 'heading' => ['cz' => 'Hmotnost', 'sk' => 'Hmotnosť']],
      self::COLUMN_ID                   => ['column' => 'Identification', 'heading' => ['cz' => 'ID výrobku', 'sk' => 'ID tovaru']],
      self::COLUMN_SELLOUT              => ['column' => 'Sellout', 'heading' => ['cz' => 'Výprodej', 'sk' => 'Výpredaj']],
    ];
    
    private $columnsDefault = [
      self::COLUMN_ID => NULL,
      self::COLUMN_PRODUCER => NULL, 
      self::COLUMN_CATALOG => NULL, 
      self::COLUMN_RETAIL_PRICE => NULL, 
      self::COLUMN_PRICE => NULL, 
      self::COLUMN_PRICE_OK => NULL, 
      self::COLUMN_STOCK => NULL,
      self::COLUMN_SELLOUT => NULL,
    ];
    
    protected $translations = [
      'yes' => ['CZ' => 'Ano', 'SK' => 'Áno'],
      'no'  => ['CZ' => 'Ne', 'SK' => 'Nie']
    ];
    
    private $noConstraints = [];
    protected $constraints = [];
    
    private $_ITEMS = [];
    private $_ITEM_PRICES = [];
    private $_PARTNER_PRICES = [];
    
    /** @var \DOMDocument */
    protected $_XML;
    /** @var DOMElement */
    protected $_ROOT;
    /** @var DOMXPath */
    protected $_XPATH;
    protected $_DATA = [];
    /** @var DateTime */
    protected $_NOW = NULL;
    
    
    protected $identification = NULL;
    /** @var PartnerEntity */
    protected $partner = NULL;
    /** @var string */
    protected $partnerEngine = '';
    /** @var PartnerProducerEntity[] */
    protected $partnerProducers = '';
    
    private $timer = NULL;
    protected $logger;

    public function __construct($dir = NULL) {
      $this->timer = microtime(TRUE);
      
      
      $this->requestType = ((filter_input(INPUT_GET, 'identification')) ? static::REQUEST_TYPE_GET : static::REQUEST_TYPE_POST);
      $this->_DATA = filter_input_array(constant('INPUT_' . strtoupper($this->requestType)));
      $this->isAdmin = (bool)Functions::item($this->_DATA, 'admin', FALSE);
      
      $this->_NOW = new DateTime();
      
      $this->partnerDao   = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);
      $this->categoryDao  = Agents::getAgent('SKCZ_CategoryDao', Agents::TYPE_MODEL);
      $this->productDao   = Agents::getAgent('ProductDao', Agents::TYPE_MODEL);
      
      $this->identification = Functions::item($this->_DATA, 'identification');
      $this->partner = $this->partnerDao->load($this->identification);
      $this->partnerEngine = $this->partner->styleplusPartner->engine;
      $this->partnerProducers = $this->partner->producers->getLoadedArrayCopy();
      
$this->logger = \prosys\core\common\Logger::create('generator_', 'log_' . $this->identification . '_' . date('Ymd-His'), __DIR__ . '/log/');
$this->logger->append(['msg' => 'zahájení zpracování', 'data' => $this->_DATA])->store();

      $this->noConstraints = [
        'modified_from' => NULL, 'categories' => TRUE, 'producers' => TRUE,
        'catalog' => TRUE, 'force_rel' => FALSE, 'show_removed' => FALSE,
        'stock_count' => FALSE, 'images' => TRUE, 'size' => 'big',
        'related' => TRUE, 'default_filter' => [], 'timelimit' => 60,
        'columns' => $this->columnsDefault, 'category_path' => TRUE,
        'allowed_request_type' => [static::REQUEST_TYPE_POST]
      ];
      $this->constraints = $this->partner->getConstraintsDefault()->jsonSerialize() + $this->noConstraints;
      if ($this->partner->type == PartnerEntity::TYPE_OLD) {
        $this->constraints['allowed_request_type'] = [self::REQUEST_TYPE_GET, self::REQUEST_TYPE_POST];
      }
      array_walk($this->constraints['columns'], function(&$column, $key) {
        $column = ((is_null($column)) ? $this->allColumnsDefinition[$key] : $column);
      });
      $data = $this->constraints;
      $newData = ['modified_from' => date('Y-m-d H:00:00', strtotime(Functions::unsetItem($data, 'modified_from')))] + $data; 
      
      $this->dir = $this->createDir(rtrim(((is_null($dir)) ? __DIR__ . '/../../../xml/new/' : $dir), '/') . '/');
      $this->fileName = (($this->isAdmin()) ? 'admin_' : '') . md5($this->partner->hashCode . json_encode($newData) . date('YmdH', strtotime('-' . self::CACHE_TIME))) . '_' . Functions::seoTypeConversion($this->partner->name) . '.xml';
      $this->filePath = $this->dir . $this->fileName; 
      $this->clearCacheDir();
      
      $this->_XML = new DOMDocument('1.0', 'UTF-8');
      $this->_XML->formatOutput = TRUE;
        $this->_ROOT = $this->_XML->createElement('ResponseData');
        $this->appendChild($this->_ROOT, 'DateTime', $this->_NOW->format('Y-m-dTH:i:s'));
        
      if (!in_array($this->requestType, (array)Functions::item($newData, 'allowed_request_type', []))) {
        $this->sendAccessDenied();
      }
    }
    
    private function loadItems() {
      if (!$this->_ITEMS) {
        $filter = \prosys\core\mapper\SqlFilter::create()->identity();
        
        if (!$this->partner->showSellout) {
          $filter->andL()->comparise('sellout', '=', '0');
        }
        
        $this->_ITEMS = $this->partnerDao->loadPartnerProductsNew(
          $this->partner,
          $filter,
          $this->constraints['modified_from'],
          $this->constraints['show_removed'],
          array_map(function($partnerProducer) {
            /* @var $partnerProducer PartnerProducerEntity */
            return [
              'id' => $partnerProducer->producer->id,
              'modifiedAt' => $partnerProducer->modifiedAt,
            ];
          }, array_filter($this->partnerProducers, function($partnerProducer) {
            /* @var $partnerProducer PartnerProducerEntity */
            return $partnerProducer->modifiedAt >= (new \DateTime($this->constraints['modified_from']));
          }))
        );
      }
      
      return $this->_ITEMS;
    }
    
    public function loadProductPrices() {
$this->logger->append('loadProductPrices start')->store();
      if (!$this->_ITEM_PRICES) {
        if ($this->partner->pricesFromOrders) {
$this->logger->append('loadProductPrices price from orders')->store();
          $ordersPrices = $this->partner->getProductPricesFromOrders();

          foreach ($this->loadItems() as $item) {
            $this->_ITEM_PRICES[$item['id']] = Functions::item($ordersPrices, [$item['producer_id'], $item['catalog_id']], 0);

            foreach ($item['variants'] AS $variant) {
              $this->_ITEM_PRICES[$variant['id']] = Functions::item($ordersPrices, [$item['producer_id'], $variant['catalog_id']], 0);
            }
          }
        } else {
$this->logger->append('loadProductPrices normal prices')->store();
          // stahne idcka vsech variant vyrobku
          $getChildIds = function($variants) {
            return array_map(
              function($child) { return $child['id']; },
              $variants
            );
          };

$this->logger->append('loadProductPrices -> partner prices load')->store();
          
          $arrayIds = [];
          foreach ($this->loadItems() as $item) {
            $arrayIds[] = $item['id'];
            if (array_key_exists('variants', $item) && is_array($item['variants'])) {
              foreach ($item['variants'] as $variant) {
                $arrayIds[] = $variant['id'];
              }
            }
          }
          
$this->logger->append(['count ids_to_prices' => count($arrayIds)])->store();
          // stahne ceny vyrobku partnera
          $this->_ITEM_PRICES = $this->partnerDao->loadPartnerPrices(
            $this->partner,
            $arrayIds
          );
          $this->logger->append('loadProductPrices -> partner prices loaded')->store();

          if (!$this->_PARTNER_PRICES) {
            $this->logger->append('loadProductPrices -> partner netto prices load')->store();
            $this->_PARTNER_PRICES = $this->partner->getNettoPrices();     // [product_id => price]
            $this->logger->append('loadProductPrices -> partner netto prices loaded')->store();
          }
        }
      }
      
$this->logger->append('loadProductPrices end')->store();
      
      return $this->_ITEM_PRICES;
    }
    
    /**
     * Vrátí čas od začátku zporacování scriptu.
     * 
     * @return float
     */
    protected function getProcessTime() {
      return microtime(TRUE) - $this->timer;
    }
    
    /**
     * Vypise nebo ulozi XML.
     * 
     * @param bool $save
     */
    private function closeXml($save = FALSE) {
$this->logger->append('closeXml start')->store();
      
      $this->_XML->appendChild($this->_ROOT);
      
      $info = [];
      $info['generate_time'] = round($this->getProcessTime(), 2) . 's';
      
      array_walk($info, function(&$item, $key) {
        $item = $key . ': ' . $item;
      });
      $this->_XML->insertBefore($this->_XML->createComment(
        implode(', ', $info)
      ), $this->_ROOT);
      
      if ($save) {
        $this->_XML->save($this->filePath);
        chmod($this->filePath, 0777);
        return $this->_XML;
      } else {
        header('Content-type: text/xml; charset=UTF-8');
        echo $this->_XML->saveXML();
        exit();
      }
    }
    
    /**
     * Vygeneruje identifikator vyrobku pro partnera.
     * 
     * @param int $productId
     * @return string
     */
    protected function getProductIdentification($productId) {
      return md5($this->partner->hashCode . $productId);
    }


    /**
     * Generuje XML s daty.
     */
    private function generateXml() {
      if ($this->partner->isNew() || !$this->partner->active) {
        $this->sendAccessDenied();
      } else {
$this->logger->append('generateXml start')->store();
        $this->loadProductPrices();
$this->logger->append('generateXml after loadProductPrices')->store();
        $this->statusMessage('OK', 200);
        
        $this->appendChild($this->_ROOT, 'Count', count($this->_ITEMS));
        $productSet = $this->appendChild($this->_ROOT, 'ProductSet');
$this->logger->append('generateXml before foreach')->store();
        foreach ($this->_ITEMS as $idx => $itemData) {
          /* @var $item ProductEntity */
          $item = $this->productDao->load($itemData, FALSE);
          $item->variants = $itemData['variants'];
          
          // je-li nadvyrobek, pak je odstraneny v pripade, ze jsou odstranene vsechny varianty
          if (count($item->variants)) {
            $removed = (int)!array_filter($item->variants, function($variant) {
              return !$variant['unavailable'];
            });
          } else { 
            $removed = (int)$item->unavailable;
          }
          
          if ($removed && !$this->constraints['show_removed']) { continue; }
          
          $product = $this->appendChild($productSet, 'Product');
            // nastavitelne sloupce
            foreach ($this->constraints['columns'] as $column => $heading) {
              $current = $this->allColumnsDefinition[$column]['column'];

              /* @var $partnerProductProducer PartnerProducerEntity */
              $partnerProductProducer = Functions::first(array_filter($this->partnerProducers, function($partnerProducer) use ($item) {
                return $partnerProducer->producer->id == $item->producerId;
              }));

              $currentModifiedAt = (($partnerProductProducer && $partnerProductProducer->modifiedAt) ? max($item->modifiedAt, $partnerProductProducer->modifiedAt) : $item->modifiedAt);

              // uzly, ktere jsou videt u smazanych i nesmazanych vyrobku
              switch ($current) {
                case 'Identification':   $this->appendChild($product, 'Identification', $this->getProductIdentification($item->id)); break;
                case 'Producer':   $this->appendChild($product, 'Producer', $item->producer);                             break;
                case 'Catalog':    if (!$item->variants) { $this->appendChild($product, 'Catalog', $item->catalogId); }   break;
                case 'ModifiedAt': $this->appendChild($product, 'ModifiedAt', $currentModifiedAt->format('Y-m-dTH:i:s')); break;
                //case 'ModifiedAt': $_XML->createSimpleElement('ModifiedAt', $item->modifiedAt->format('Y-m-dTH:i:s')); break;
                case 'Removed':    $this->appendChild($product, 'Removed', $removed);         break;
              }

              // uzly, ktere jsou viditelne jen u nesmazanych / aktivnich vyrobku
              if (!$removed) {
                // uzly produktu, ktere nemaji varianty
                if (!$item->variants) {
                  switch ($current) {
                    case 'EAN':         $this->appendChild($product, 'EAN', $item->ean);                                                     break;
                    case 'RetailPrice': $this->appendChild($product, 'RetailPrice', $this->partner->getProductInformation($item, 'retailPrice')); break;
                    case 'Price':       $this->appendChild($product, 'Price', Functions::item($this->_ITEM_PRICES, $item->id, ''));              break;
                    case 'Guarantee':   $this->appendChild($product, 'Guarantee', $item->guarantee);                                         break;
                    case 'Delivery':    $this->appendChild($product, 'Delivery', $item->delivery);                                           break;
                    case 'Weight':      $this->appendChild($product, 'Weight', (($item->weight) ? $item->weight : ''));                      break;
                    case 'EshopPrice':
                      if (!($eshopPrice = (float)Functions::item($this->_PARTNER_PRICES, $item->id))) {
                        $priceOk = (($this->partner->useRegularOkPrices) ? $this->partner->getProductInformation($item, 'okPrice') : $item->getRealOkPrice($this->partnerEngine));
                        $price = (float)Functions::item($this->_ITEM_PRICES, $item->id, '');

                        $productProfit = (($partnerProductProducer) ? $partnerProductProducer->profit : self::MIN_PROFIT_DEFAULT);

                        $minProfit = ($productProfit * $price) / 100;
                        $eshopPrice = ((($price + $minProfit) < $priceOk) ? $priceOk : ($price + $minProfit)); 
                      }

                      $this->appendChild($product, 'EshopPrice', $eshopPrice);
                      $this->appendChild($product, 'RecommendedPrice', $eshopPrice);
                    break;
                    case 'PriceOK':
                      $price = (($this->partner->useRegularOkPrices) ? $this->partner->getProductInformation($item, 'okPrice') : $item->getRealOkPrice($this->partnerEngine));
                      $this->appendChild($product, 'PriceOK', (($price) ? $price : 'NULL'));
                    break;
                    case 'RetailPriceForeign':
                      $foreign = 'retailPrice' . ((strtolower($this->partnerEngine) == 'sk') ? 'CZ' : 'SK');
                      $this->appendChild($product, 'RetailPriceForeign', (($item->$foreign) ? $item->$foreign : 'NULL'));
                    break;
                    case 'PriceForeign':
                      $lang = ((strtolower($this->partnerEngine) == 'sk') ? 'CZ' : 'SK');
                      $foreign = 'okPrice' . $lang;
                      $price = (($this->partner->useRegularOkPrices) ? $item->$foreign : $item->getRealOkPrice($lang));

                      $this->appendChild($product, 'PriceForeign', (($price) ? $price : 'NULL'));
                    break;
                    case 'Stock':
                      $stock = (($this->constraints['stock_count']) ?
                                  $item->stock :
                                  (($item->stock) ?
                                      $this->translations['yes'][$this->partnerEngine] :
                                      $this->translations['no'][$this->partnerEngine]));
                      $this->appendChild($product, 'Stock', $stock);
                    break;
                    case 'StockCount':
                      $stockText = '';
                      switch ($item->stock) {
                        case 0:
                        case $item->stock <= 5:   $stockText = $item->stock;  break;
                        case $item->stock <= 10:  $stockText = '> 5';         break;
                        case $item->stock <= 100: $stockText = '> 10';        break;
                        default:                  $stockText = '> 100';       break;
                      }

                      $this->appendChild($product, 'StockCount', $stockText);
                    break;
                    case 'Sellout':
                      $this->appendChild($product, 'Sellout', Functions::item($this->translations, [(($item->sellout) ? 'yes' : 'no'), $this->partnerEngine], $item->sellout));
                    break;
                  }
                  
                }

                // uzly spolecne pro produkty, ktere maji varianty i produkty bez variant
                switch ($current) {
                  case 'Serie':       $this->appendChild($product, 'Serie', $this->partner->getProductInformation($item, 'serie'), TRUE);               break;
                  case 'ProductName': $this->appendChild($product, 'ProductName', $this->partner->getProductInformation($item, 'nameWholesale')); break;
                  case 'Name':        $this->appendChild($product, 'Name', $this->partner->getProductInformation($item, 'name'));                 break;
                  case 'Description': $this->appendChild($product, 'Description', $this->partner->getProductInformation($item, 'description'), TRUE);   break;
                  case 'Category':
                    $this->appendChild($product, 'Category', (($this->constraints['category_path']) ?
                      $this->partner->getProductInformation($item, 'categoryPath') :
                      $this->partner->getProductInformation($item, 'category'))
                    );
                  break;
                }
              }
            }
            
            // varianty vyrobku
            $variants = $this->appendChild($product, 'Variants');
              foreach ($item->variants as $variantData) {
                $variant = $this->productDao->load($variantData, FALSE);    /* @var $variant ProductEntity */

                if ($variant->catalogId) {
                  $variantRemoved = (int)$variant->unavailable;

                  $variantElement = $this->appendChild($variants, 'Variant');
                    foreach ($this->constraints['columns'] as $column => $heading) {
                      $current = $this->allColumnsDefinition[$column]['column'];

                      // uzly, ktere jsou videt u smazanych i nesmazanych podvyrobku
                      switch ($current) {
                        case 'Identification':   $this->appendChild($variantElement, 'Identification', $this->getProductIdentification($variant->id)); break;
                        case 'Catalog':     $this->appendChild($variantElement, 'Catalog', $variant->catalogId); break;
                        //case 'ModifiedAt':  $_XML->createSimpleElement('ModifiedAt', $variant->modifiedAt->format('Y-m-dTH:i:s'));                  break;
                        case 'ModifiedAt':
                          $variantModifiedAt = (($partnerProductProducer) ? max($variant->modifiedAt, $partnerProductProducer->modifiedAt) : $variant->modifiedAt);
                          $this->appendChild($variantElement, 'ModifiedAt', $variantModifiedAt->format('Y-m-dTH:i:s'));
                        break;
                        case 'Removed':     $this->appendChild($variantElement, 'Removed', $variantRemoved);     break;
                      }

                      // uzly, ktere jsou viditelne jen u nesmazanych / aktivnich vyrobku
                      if (!$variantRemoved) {
                        switch ($current) {
                          case 'EAN':         $this->appendChild($variantElement, 'EAN', $variant->ean);                                                            break;
                          case 'ProductName': $this->appendChild($variantElement, 'VariantName', $this->partner->getProductInformation($variant, 'nameWholesale')); break;
                          case 'RetailPrice': $this->appendChild($variantElement, 'RetailPrice', $this->partner->getProductInformation($variant, 'retailPrice'));   break;
                          case 'Price':       $this->appendChild($variantElement, 'Price', Functions::item($this->_ITEM_PRICES, $variant->id, ''));                     break;
                          case 'Guarantee':   $this->appendChild($variantElement, 'Guarantee', $variant->guarantee);                                                break;
                          case 'Delivery':    $this->appendChild($variantElement, 'Delivery', $variant->delivery);                                                  break;
                          case 'Weight':      $this->appendChild($variantElement, 'Weight', (($variant->weight) ? $variant->weight : ''));                          break;
                          case 'EshopPrice':
                            if (!($eshopPrice = (float)Functions::item($this->_PARTNER_PRICES, $variant->id))) {
                              $priceOk = (($this->partner->useRegularOkPrices) ? $this->partner->getProductInformation($variant, 'okPrice') : $variant->getRealOkPrice($this->partnerEngine));
                              $price = (float)Functions::item($this->_ITEM_PRICES, $variant->id, '');

                              $productProfit = self::MIN_PROFIT_DEFAULT;
                              $partnerProductProducer = Functions::first(array_filter($this->partnerProducers, function($partnerProducer) use ($variant) {
                                return $partnerProducer->producer->id == $variant->producerId;
                              }));

                              if ($partnerProductProducer) {
                                $productProfit = $partnerProductProducer->profit;
                              }

                              $minProfit = $productProfit * $price / 100;
                              $eshopPrice = ((($price + $minProfit) < $priceOk) ? $priceOk : ($price + $minProfit));
                            }

                            $this->appendChild($variantElement, 'EshopPrice', $eshopPrice);
                            $this->appendChild($variantElement, 'RecommendedPrice', $eshopPrice);
                          break;
                          case 'PriceOK':
                            $priceOk = (($this->partner->useRegularOkPrices) ? $this->partner->getProductInformation($variant, 'okPrice') : $variant->getRealOkPrice($this->partner->styleplusPartner->engine));
                            $this->appendChild($variantElement, 'PriceOK', (($priceOk) ? $priceOk : 'NULL'));
                          break;
                          case 'RetailPriceForeign':
                            $foreign = 'retailPrice' . ((strtolower($this->partnerEngine) == 'sk') ? 'CZ' : 'SK');
                            $this->appendChild($variantElement, 'RetailPriceForeign', (($variant->$foreign) ? $variant->$foreign : 'NULL'));
                          break;
                          case 'PriceForeign':
                            $lang = ((strtolower($this->partnerEngine) == 'sk') ? 'CZ' : 'SK');
                            $foreign = 'okPrice' . $lang;
                            $price = (($this->partner->useRegularOkPrices) ? $variant->$foreign : $variant->getRealOkPrice($lang));

                            $this->appendChild($variantElement, 'PriceForeign', (($price) ? $price : 'NULL'));
                          break;
                          case 'Stock':
                            // je na sklade
                            $stock = (($this->constraints['stock_count']) ?
                                        $variant->stock :
                                        (($variant->stock) ?
                                            $this->translations['yes'][$this->partnerEngine] :
                                            $this->translations['no'][$this->partnerEngine]));
                            $this->appendChild($variantElement, 'Stock', $stock);
                          break;
                          case 'StockCount':
                            $stockText = '';
                            switch ($variant->stock) {
                              case 0:
                              case $variant->stock <= 5:   $stockText = $variant->stock; break;
                              case $variant->stock <= 10:  $stockText = '> 5';           break;
                              case $variant->stock <= 100: $stockText = '> 10';          break;
                              default:                     $stockText = '> 100';         break;
                            }

                            $this->appendChild($variantElement, 'StockCount', $stockText); 
                          break;
                          case 'Sellout':
                            $this->appendChild($variantElement, 'Sellout', Functions::item($this->translations, [(($variant->sellout) ? 'yes' : 'no'), $this->partnerEngine], $variant->sellout));
                          break;
                        } 
                        
                      }
                    }
                  }
                }
              
                if (!$removed) {
                  // obrazky
                  if ($this->constraints['images'] === TRUE) {
                    $root = 'http://www.stylovekoupelny.cz/photos/';

                    switch ($this->constraints['size']) {
                      case 'sp':
                        $type = 'velka';
                        $dir = (($this->partner->noWatermark) ? 'velke/' : 'sp/');
                        $size = 'big';
                      break;

                      case 'original': $type = 'original'; $dir = 'original/'; $size = 'original'; break;
                      case 'big':      $type = 'original'; $dir = 'velke/';    $size = 'original'; break;
                      case 'medium':   $type = 'velka';    $dir = 'stredni/';  $size = 'big';      break;
                      default:         $type = 'mala';     $dir = 'male/';     $size = 'small';    break;
                    }

                    $firstImage = NULL;
                    $images = $this->appendChild($product, 'Images');
                      foreach ($item->images as $image) {
                        if (is_null($firstImage)) { $firstImage = $image; }
                        $modification = (($image['modification']) ? $image['modification']->format('Y-m-dTH:i:s') : '');
                        $this->appendChild($images, 'Image', $root . $dir . $image['src'])
                          ->setAttribute('modification', $modification);
                      }
  
                    $this->appendChild($product, 'ImageFeed', $root . 'sp-small/' . $firstImage['src']);
                  }

                  // souvisejici vyrobky
                  if ($this->constraints['related'] === TRUE) {
                    $related = $this->appendChild($product, 'Related');
                      foreach ((array)$item->related as $relatedItem) {
                        $item = $this->appendChild($related, 'Item');
                          $this->appendChild($item, 'Producer', $relatedItem['producer']);
                          $this->appendChild($item, 'Catalog', $relatedItem['catalog_id']);
                      }
                  }
                }
        }
        
$this->logger->append('generateXml after foreach')->store();
        
        $this->logAccess();
        
        $this->closeXml(TRUE);
      }
    }
    
    public function generate() {
      $this->logger->append('generate start')->store();
      if (
        !(bool)Functions::item($this->_DATA, 'force_generate', FALSE) && 
        $this->isCached($this->filePath) &&
        time() < (filemtime($this->filePath) + ($this->constraints['timelimit'] * 60))) {
$this->logger->append('generate isCache')->store();
        $this->_XML->load($this->filePath);
        $this->_XPATH = new DOMXPath($this->_XML);
        
        $status = $this->_XPATH->query('//Status')->item(0);
        $this->statusCode = $this->_XPATH->query('Code', $status)->item(0)->nodeValue;
        $this->statusMessage = $this->_XPATH->query('Message', $status)->item(0)->nodeValue;
        
        $this->logAccess([
          'load_from_cache' => TRUE,
          'count' => (int)$this->_XPATH->query('//Count')->item(0)->nodeValue, 
        ]);
      } else {
$this->logger->append('generate !isCache')->store();
        $this->generateXml();
        $this->_XPATH = new DOMXPath($this->_XML);
      }
    }
    
    protected function isCached($file) {
      return file_exists($file) && !(filemtime($file) < strtotime('-' . self::CACHE_TIME));
    }
  
    /**
     * Vrátí skupiny souborů dle data
     *
     * @param $dir
     *
     * @return array
     * [
     *  '2018-01-01' => [
     *    '/etc/dir/..../file.xml',
     *    ...
     *  ],
     *  ...
     * ]
     */
    private function getFileGroups($dir) {
      $groups = [];
      
      foreach (glob($dir . '*.xml') as $file) {
        $filemtime = filemtime($file);
        $date = date('Y-m-d', $filemtime);
        if (date('YmdHis', $filemtime) >= date('YmdHis', time() - ($this->constraints['timelimit'] * 60))) { continue; }
        
        if (!array_key_exists($date, $groups)) { $groups[$date] = []; }
        $groups[$date][] = $file;
      }
      
      return $groups;
    }
  
    public function gzipWithOutCurrentDay() {
      $path = dirname($this->filePath) . '/';
      $archiveDir = $path . 'archive/';
      if (!is_dir($archiveDir)) { Functions::mkDir($archiveDir); }
      
      $groups = $this->getFileGroups($path);
      foreach ($groups as $date => $files) {
        $archiveDayDir = $archiveDir . $date . '/';
        if (!is_dir($archiveDayDir)) { Functions::mkDir($archiveDayDir); }
        
        foreach ($files as $file) {
          rename($file, $archiveDayDir . basename($file));
        }
  
        Zip::zipDir($archiveDayDir, $archiveDir . basename($archiveDayDir) . '.zip');
        Functions::deleteDir($archiveDayDir);
      }
      
      var_dump('Vytvorene archivy:');
      var_dump(array_keys($groups));
    }
    
    private function clearCacheDir() {
      foreach (glob($this->dir . '*') as $file) {
        if (file_exists($file) && filemtime($file) < strtotime('-' . self::CACHE_DELETE_TIME)) {
          $this->deleteFile($file);
        }
      }
    }

    private function deleteFile($file) {
      if (file_exists($file)) {
        unlink($file);
      }
      
      return $file;
    }
    
    /**
     * Zjistí zda požadavek je POSTem.
     * 
     * @return boolean
     */
    protected function isPost() {
      return $this->requestType == static::REQUEST_TYPE_POST;
    }
    
    /**
     * Zjistí zda požadavek je GETem.
     * 
     * @return boolean
     */
    protected function isGet() {
      return $this->requestType == static::REQUEST_TYPE_GET;
    }
    
    /**
     * Vytvoří složku na předané cestě.
     * 
     * @param string $path
     * @return string
     */
    protected function createDir($path) {
      if (!is_dir($path)) {
        $oldUmask = umask(0);
          if (!@mkdir($path, 0775, TRUE)) {
            throw new AppException('Nepodařilo se vytvořit adresář `' . $path . '`.');
          }
        umask($oldUmask);
      }
      
      return $path;
    }
    
    /**
     * Zapise do XML hlavicky chybovy uzel a vypise na obrazovku
     * 
     * @param string $msg
     * @param int $code
     */
    private function statusMessage($msg, $code = NULL) {
      $this->statusMessage = $msg;
      $this->statusCode = $code;
      
      $serverStatus = $this->appendChild($this->_ROOT, 'Status');
        if ($code) { $this->appendChild($serverStatus, 'Code', $code); }
        $this->appendChild($serverStatus, 'Message', $msg);
    }
    
    protected function sendAccessDenied() {
      $this->statusMessage('Access denied!', 401);
      $this->logAccess();
        
      $this->closeXml();
    }

    /**
     * Zjistí zda je pozadavek z adminu.
     * 
     * @return boolean
     */
    protected function isAdmin() {
      return (bool)$this->isAdmin;
    }
    
    /**
     * Vloží potomka do předaného DOMElementu.
     * 
     * @param DOMElement $element
     * @param string $name
     * @param mixed $value
     * @param bool $cData
     * 
     * @return DOMElement
     */
    protected function appendChild($element, $name, $value = NULL, $cData = FALSE) {
      $newElement = $this->_XML->createElement($name);
      if (!is_null($value)) {
        if ($cData) {
          $newElement->appendChild($this->_XML->createCDATASection($value));
        } else {
          $newElement->appendChild($this->_XML->createTextNode($value));
        }
      }
      
      $element->appendChild($newElement);
      
      return $newElement;
    }
    
    public function logAccess($response = NULL) {
      $response = array_merge([
          'status' => [
            'code' => $this->statusCode,
            'message' => $this->statusMessage
          ]
        ], (array)((is_null($response)) ? [
          'count' => count($this->_ITEMS)
        ] : $response));
      
      if (!$this->partner->isNew()) {
        /* @var $partnerAccessDao PartnerAccessDao */
        $partnerAccessDao = Agents::getAgent('PartnerAccessDao', Agents::TYPE_MODEL);

        /* @var $log PartnerAccessEntity */
        $log = $partnerAccessDao->load([
          'partner_id' => $this->partner->user->id,
          'access_at'  => new DateTime(),
          'ip'         => $_SERVER['REMOTE_ADDR'],
          'proxy_ip'   => Functions::getProxyIpAddress(),
          'method'     => $this->requestType,
          'params'     => $this->_DATA,
          'response'   => $response
        ]);

        // ulozi log
        $partnerAccessDao->store($log);

        // smaze stare logy v DB
        $partnerAccessDao->deleteRecords(
          SqlFilter::create()
            ->comparise('access_at', '<', date('Y-m-d H:i:s', strtotime('-60 days')))
        );
      }
    }
    
    /** @inheritDoc */
    public function response($activity) { throw new AppException('Method ' . __METHOD__ . ' not allowed!'); }
    
    /**
     * Vytvoří instanci generatoru dle typu.
     * 
     * @return General_GeneratorController
     */
    public static function createGenerator() {
      $archive = filter_input(INPUT_GET, 'archiveFeeds');
      
      $idPost = filter_input(INPUT_POST, 'identification');
      $idGet  = filter_input(INPUT_GET, 'identification');
      $outputTypeGet = filter_input(INPUT_GET, 'output_type');
      
      /* @var $partnerDao PartnerDao */
      $partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);

      /* @var $partner PartnerEntity */
      $partner = $partnerDao->load((($idPost) ? $idPost : $idGet));
      $constraints = $partner->getConstraintsDefault()->jsonSerialize();
      $outputType = (($outputTypeGet) ? $outputTypeGet : (($partner->type == PartnerEntity::TYPE_OLD && Functions::item($constraints, 'excel', TRUE)) ? 'excel' : ''));
      
      switch ($outputType) {
        case 'excel': $generator = 'Excel_';  break;
        case 'csv':   $generator = 'Csv_';    break;
        case 'xml':   
        default:      $generator = '';        break;
      }
      
      $instance = Agents::getAgent($generator . 'GeneratorController', Agents::TYPE_CONTROLLER_ADMIN);
      if ($archive) { $instance->gzipWithOutCurrentDay(); exit(); }
      
      return $instance;
    }
  }