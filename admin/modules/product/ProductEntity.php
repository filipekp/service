<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu vyrobce.
   * 
   * @property int $id
   * @property int $producerId element=producer_id
   * @property string $producer
   * @property string $categoryCZ element=category_cz
   * @property string $categoryPathCZ element=category_path_cz
   * @property string $categorySK element=category_sk
   * @property string $categoryPathSK element=category_path_sk
   * @property string $serieCZ element=serie_cz
   * @property string $serieSK element=serie_sk
   * @property int $parent
   * @property string $catalogId element=catalog_id
   * @property string $ean
   * @property string $nameCZ element=name_cz
   * @property string $nameWholesaleCZ element=name_wholesale_cz
   * @property string $nameSK element=name_sk
   * @property string $nameWholesaleSK element=name_wholesale_sk
   * @property string $descriptionCZ element=description_cz
   * @property string $descriptionSK element=description_sk
   * @property float $retailPriceCZ element=retail_price_cz
   * @property float $retailPriceSK element=retail_price_sk
   * @property float $okPriceCZ element=okcz_price
   * @property float $okPriceServiceCZ element=okcz_service_price
   * @property float $okPriceSK element=oksk_price
   * @property float $okPriceServiceSK element=oksk_service_price
   * @property int $guarantee
   * @property int $stock
   * @property int $delivery
   * @property \DateTime $modifiedAt element=modified_at
   * @property float $weight
   * @property bool $unavailable delete_flag
   * @property string $images
   * @property string $related
   * @property bool $sellout
   * 
   * @property ProductEntity[] $variants noelement
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ProductEntity extends Entity
  {
    const SALE_CHANNEL_ALL = 1;
    const SALE_CHANNEL_CZ = 2;
    const SALE_CHANNEL_SK = 3;
    
    /** @var ProductEntity[] */
    private $_variants;
    
    /**
     * Vrati "opravdovou" cenu ze serveru onlinekoupelen - na serveru je zvlastni tabulka s urcenymi cenami, ktera ma pred "beznou" cenou prednost.
     * 
     * @return float
     */
    public function getRealOkPrice($lang) {
      $servicePriceSlot = 'okPriceService' . $lang;
      $priceSlot = 'okPrice' . $lang;
      
      return (($this->$servicePriceSlot) ? $this->$servicePriceSlot : $this->$priceSlot);
    }

    /**
     * Getter.
     * @return array
     */
    public function getImages() {
      if (is_string($this->_properties['images']['value'])) {
        $this->_properties['images']['value'] = explode('|', $this->_properties['images']['value']);
        array_walk($this->_properties['images']['value'], function(&$image) {
          list($src, $modification) = explode('/', $image);
          $image = array(
            'src'           => $src,
            'modification'  => (($modification) ? new \DateTime($modification) : NULL)
          );
        });
      }
      
      return (array)$this->_properties['images']['value'];
    }
    
    /**
     * Getter.
     * @return array
     */
    public function getRelated() {
      if (is_string($this->_properties['related']['value']) && $this->_properties['related']['value']) {
        $related = explode('|', $this->_properties['related']['value']);
        array_walk($related, function(&$item) {
          if (count(explode('@', $item)) !== count(['catalog_id', 'producer'])) {
            @\prosys\core\common\Mailer::sendMail('Souvisejici vyrobky - nesedi count', $item, 'podpora@proclient.cz', 'svezi@proclient.cz');
          }

          $item = @array_combine(['catalog_id', 'producer'], explode('@', $item));
        });
        
        $this->_properties['related']['value'] = $related;
      }
      
      return $this->_properties['related']['value'];
    }
    
    /**
     * Vrati seznam variant vyrobku, kde odfiltruje "sam sebe".<br />
     * Sama sebe pozna dle katalogoveho cisla: katalogove cislo je ruzne od katalogoveho cisla rodice
     * a vyrobek s variantami nema zadne katalogove cislo.
     * 
     * @return ProductEntity[]
     */
    public function getProductVariants() {
      return array_filter($this->variants->getLoadedArrayCopy(), function($item) {
        return $item->catalogId && $item->id != $item->parent;
      });
    }
    
    /**
     * Getter.
     * @return ProductEntity[]
     */
    public function getVariants() {
      return (array)$this->_variants;
    }
    
    /**
     * Setter.
     * 
     * @param ProductEntity[] $variants
     * @return \prosys\model\ProductEntity
     */
    public function setVariants(array $variants) {
      $this->_variants = $variants;
      return $this;
    }
  }
