<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Twig\Node\Node;

/**
 * Extracts constraint messages from GraphQl config yaml files.
 *
 * Example:
 *  Mutation:
 *      type: object
 *      config:
 *          fields:
 *              NewsletterSubscribe:
 *                  type: Boolean!
 *                  description: "Subscribe for e-mail newsletter"
 *                  args:
 *                      input:
 *                          type: "String!"
 *                          validation:
 *                              -   NotBlank:
 *                                      message: "Please enter email"
 */
class ConfigConstraintMessageExtractor implements FileVisitorInterface
{
    /**
     * @inheritdoc
     */
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
        if ($file->getExtension() === 'yaml' || $file->getExtension() === 'yml') {
            $yamlContent = Yaml::parseFile($file->getRealPath(), Yaml::PARSE_CUSTOM_TAGS);
            if ($yamlContent !== null) {
                $validationArrays = $this->getAllValuesOfArrayKeysByPattern($yamlContent, '/^validation$/');
                $validationArraysWithoutCascade = array_filter($validationArrays, static function ($value) {
                    return $value !== 'cascade';
                });
                $messages = $this->getAllValuesOfArrayKeysByPattern($validationArraysWithoutCascade, '/.*message.*/i');
                foreach ($messages as $message) {
                    // message value can be null or ~
                    if (is_string($message)) {
                        $catalogue->add(new Message($message, Translator::VALIDATOR_TRANSLATION_DOMAIN));
                    }
                }
            }
        }
    }

    /**
     * @param array $yamlContent
     * @param string $pattern
     * @return array
     */
    protected function getAllValuesOfArrayKeysByPattern(array $yamlContent, string $pattern): array
    {
        $iterator = new RecursiveArrayIterator($yamlContent);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        $elements = [];
        foreach ($recursive as $key => $value) {
            preg_match($pattern, (string)$key, $matches);
            if (count($matches) > 0) {
                $elements[] = $value;
            }
        }

        return $elements;
    }

    /**
     * @inheritdoc
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
    }

    /**
     * @inheritdoc
     */
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, Node $ast): void
    {
    }
}
