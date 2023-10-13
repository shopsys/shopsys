<?php

declare(strict_types=1);

namespace App\Resources\definition;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class ProductIndexMapping extends AbstractIndexMapping
{
    const ANALYZER_FULL_WITH_DIACRITIC = 'full_with_diacritic';
    const ANALYZER_FULL_WITHOUT_DIACRITIC = 'full_without_diacritic';
    const ANALYZER_EDGE_NGRAM_WITH_DIACRITIC = 'edge_ngram_with_diacritic';
    const ANALYZER_EDGE_NGRAM_WITHOUT_DIACRITIC = 'edge_ngram_without_diacritic';
    const ANALYZER_WHITESPACE = 'whitespace';
    const ANALYZER_WHITESPACE_WITHOUT_DOTS = 'whitespace_without_dots';
    const ANALYZER_EDGE_NGRAM_UNANALYZED_WORDS = 'edge_ngram_unanalyzed_words';

    public function indexSetting(DomainConfig $domainConfig)
    {
        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
            $this->mappingBuilder->setSetting('number_of_shards', 1);
            $this->mappingBuilder->setSetting('number_of_replicas', 0);
        } else {
            $this->mappingBuilder->setSetting('number_of_shards', 10);
            $this->mappingBuilder->setSetting('number_of_replicas', 4);
        }
    }

    public function analysisSetting(DomainConfig $domainConfig)
    {
        if ($domainConfig->getLocale() === 'cs') {
            $this->analysisCs();
        }

        if ($domainConfig->getLocale() === 'en') {
            $this->analysisEn();
        }
    }

    private function analysisEn()
    {
        $this->mappingBuilder->addFilter(
            'english_stop',
            'stop',
            [
                'stopwords' => '_english_',
            ],
        );

        $this->mappingBuilder->addFilter(
            'english_stemmer',
            'stemmer',
            [
                'language' => 'english',
            ],
        );

        $this->mappingBuilder->addFilter(
            'edge_ngram',
            'edgeNGram',
            [
                'min_gram' => 2,
                'max_gram' => 20,
            ],
        );

        $this->addTokenizer('keep_special_chars', 'pattern', ['pattern' => '[^\p{L}\d-/]+']);


        $this->mappingBuilder->addAnalyzer('full_with_diacritic',
            ['tokenizer' => 'keep_special_chars',
                'filter' => ['lowercase'],
            ],
        );

        $this->mappingBuilder->addAnalyzer('full_without_diacritic',
            [
                'tokenizer' => 'keep_special_chars',
                'filter' => [
                    'lowercase',
                    'asciifolding',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('stemming',
            [
                'tokenizer' => 'standard',
                'filter' => [
                    'lowercase',
                    'english_stemmer',
                    'english_stop',
                    'asciifolding',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('edge_ngram_with_diacritic',
            [
                'tokenizer' => 'keep_special_chars',
                'filter' => [
                    'edge_ngram',
                    'lowercase',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('edge_ngram_without_diacritic',
            [
                'tokenizer' => 'keep_special_chars',
                'filter' => [
                    'edge_ngram',
                    'lowercase',
                    'asciifolding',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('full_without_diacritic_html',
            [
                'char_filter' => 'html_strip',
                'tokenizer' => 'keep_special_char',
                'filter' => [
                    'lowercase',
                    'asciifolding',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('edge_ngram_without_diacritic_html',
            [
                'char_filter' => 'html_strip',
                'tokenizer' => 'keep_special_char',
                'filter' => [
                    'edge_ngram',
                    'lowercase',
                    'asciifolding',
                ],
            ],
        );

        $this->mappingBuilder->addAnalyzer('edge_ngram_unanalyzed',
            [
                'tokenizer' => 'keyword',
                'filter' => ['edge_ngram'],
            ],
        );

        $this->mappingBuilder->addAnalyzer('edge_ngram_unanalyzed_words',
            [
                'tokenizer' => 'whitespace',
                'filter' => ['edge_ngram'],
            ],
        );

        $this->mappingBuilder->addAnalyzer('whitespace_without_dots',
            [
                'tokenizer' => 'whitespace',
                'char_filter' => 'dots_replace_filter',
            ],
        );

        $this->mappingBuilder->addCharFilter('dots_replace_filter',
            [
                'type' => 'pattern_replace',
                'pattern' => '\\.',
                'replacement' => '',
            ],
        );
    }

    private function analysisCs() {
        // similar to analysisEn()
    }

    public function indexMapping(DomainConfig $domainConfig)
    {
        $searchingNames = $this->mappingBuilder->addField('searching_names', FieldType::TEXT)->setAnalyzer('stemming');
        $searchingNames->addField('full_with_diacritic', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_FULL_WITH_DIACRITIC);
        $searchingNames->addField('full_without_diacritic', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_FULL_WITHOUT_DIACRITIC);
        $searchingNames->addField('edge_ngram_with_diacritic', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_EDGE_NGRAM_WITH_DIACRITIC)
            ->setSearchAnalyzer(self::ANALYZER_FULL_WITH_DIACRITIC);
        $searchingNames->addField('edge_ngram_without_diacritic', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_EDGE_NGRAM_WITHOUT_DIACRITIC)
            ->setSearchAnalyzer(self::ANALYZER_FULL_WITHOUT_DIACRITIC);
        $searchingNames->addField('keyword', FieldType::ICU_COLLATION_KEYWORD, ['language' => $domainConfig->getLocale()])
            ->noIndex();

        $name = $this->mappingBuilder->addField('name', FieldType::TEXT);
        $name->addField('keyword', FieldType::ICU_COLLATION_KEYWORD, ['language' => $domainConfig->getLocale()])
            ->noIndex();

        $this->mappingBuilder->addField('name_prefix', FieldType::TEXT);
        $this->mappingBuilder->addField('name_sufix', FieldType::TEXT);

        $searchingCatnums = $this->mappingBuilder->addField('searching_catnums', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_WHITESPACE)
            ->setSearchAnalyzer(self::ANALYZER_WHITESPACE_WITHOUT_DOTS);
        $searchingCatnums->addField('edge_ngram_unanalyzed_words', FieldType::TEXT)
            ->setAnalyzer(self::ANALYZER_EDGE_NGRAM_UNANALYZED_WORDS)
            ->setSearchAnalyzer(self::ANALYZER_WHITESPACE_WITHOUT_DOTS);

        $this->mappingBuilder->addField('catnum', FieldType::TEXT);

        $prices = $this->mappingBuilder->addNestedField('prices');
        $prices->addProperty('pricing_group_id', FieldType::INTEGER);
        $prices->addProperty('price_with_vat', FieldType::FLOAT);
        $prices->addProperty('price_without_vat', FieldType::FLOAT);
        $prices->addProperty('vat', FieldType::FLOAT);
        $prices->addProperty('price_from', FieldType::BOOLEAN);
        $prices->addProperty('filtering_minimal_price', FieldType::FLOAT);
        $prices->addProperty('filtering_maximal_price', FieldType::FLOAT);

        $this->mappingBuilder->addField('in_stock', FieldType::BOOLEAN);
        $this->mappingBuilder->addField('is_available', FieldType::BOOLEAN);

        $parameters = $this->mappingBuilder->addNestedField('parameters');
        $parameters->addProperty('parameter_id', FieldType::INTEGER);
        $parameters->addProperty('parameter_uuid', FieldType::KEYWORD);
        $parameters->addProperty('parameter_name', FieldType::TEXT);
        $parameters->addProperty('parameter_unit', FieldType::TEXT);
        $parameters->addProperty('parameter_group', FieldType::TEXT);
        $parameters->addProperty('parameter_value_id', FieldType::INTEGER);
        $parameters->addProperty('parameter_value_uuid', FieldType::KEYWORD);
        $parameters->addProperty('parameter_value_text', FieldType::TEXT);
        $parameters->addProperty('parameter_is_dimensional', FieldType::BOOLEAN);
        $parameters->addProperty('parameter_value_for_slider_filter', FieldType::FLOAT);

        $this->mappingBuilder->addField('ordering_priority', FieldType::INTEGER);
        $this->mappingBuilder->addField('calculated_selling_denied', FieldType::BOOLEAN);
        $this->mappingBuilder->addField('selling_denied', FieldType::BOOLEAN);
        $this->mappingBuilder->addField('availability', FieldType::TEXT);
        $this->mappingBuilder->addField('availability_status', FieldType::TEXT);
        $this->mappingBuilder->addField('availability_dispatch_time', FieldType::INTEGER);
        $this->mappingBuilder->addField('is_variant', FieldType::BOOLEAN);
        $this->mappingBuilder->addField('is_main_variant', FieldType::BOOLEAN);
        $this->mappingBuilder->addField('detail_url', FieldType::TEXT);

        // ... and so on
    }

}
