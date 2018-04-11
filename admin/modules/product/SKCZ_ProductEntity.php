<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu vyrobku ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property SKCZ_CategoryEntity $category element=id_kategorie
   * @property string $nameCZ element=nazev_cz
   * @property string $nameSK element=nazev_sk
   * @property string $nameWholesaleCZ element=nazev_cz_pro_vo
   * @property string $nameWholesaleSK element=nazev_sk_pro_vo
   * @property SKCZ_ProducerEntity $producer element=id_vyrobce
   * @property SKCZ_SerieEntity $serie element=id_serie
   * @property string $catalogId element=katalogove_cislo
   * @property string $ean
   * @property string $descriptionCZ element=popis_cz
   * @property string $descriptionSK element=popis_sk
   * @property float $retailPriceCZ element=cena_moc_cz
   * @property float $retailPriceSK element=cena_moc_sk
   * @property int $deliveryTime element=dodaci_lhuta
   * @property int $stock element=kusu_skladem
   * @property int $guarantee element=zaruka_mesicu
   * @property SKCZ_ProductEntity $parent element=id_nadrazeneho_vyrobku
   * @property string $saleChannel element=id_kanalu_prodeje
   * @property \DateTime $changedAt element=zmeneno
   * @property bool $active element=aktivni
   * @property bool $deleted element=odstranen&delete_flag
   * 
   * @property OKCZ_ProductEntity $okczProduct element=eshop_id_vyrobku_cz
   * @property OKSK_ProductEntity $okskProduct element=eshop_id_vyrobku_sk
   * 
   * @property SKCZ_ProductEntity[] $variants binding=1n:>id_nadrazeneho_vyrobku:>
   * @property SKCZ_ImageEntity[] $images binding=1n:>id_vyrobku:poradi>
   * @property SKCZ_RelatedEntity[] $related binding=1n:>id_vyrobku:>
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_ProductEntity extends Entity
  {
    const SALE_CHANNEL_ALL = 1;
    const SALE_CHANNEL_CZ = 2;
    const SALE_CHANNEL_SK = 3;
    
    /**
     * Vrati seznam variant vyrobku, kde odfiltruje "sam sebe".<br />
     * Sama sebe pozna dle katalogoveho cisla: katalogove cislo je ruzne od katalogoveho cisla rodice
     * a vyrobek s variantami nema zadne katalogove cislo.
     * 
     * @return SKCZ_ProductEntity
     */
    public function getProductVariants() {
      return array_filter($this->variants->getLoadedArrayCopy(), function($item) {
        return $item->catalogId && $item->catalogId != $item->parent->catalogId;
      });
    }
  }
