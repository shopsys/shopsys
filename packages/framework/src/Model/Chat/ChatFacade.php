<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Chat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage;
use Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ChatFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatRepository $chatRepository
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatFactory $chatFactory
     * @param \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessageFactory $chatMessageFactory
     * @param \Shopsys\FrameworkBundle\Component\OpenAi\OpenAiFacade $openAiFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ChatRepository $chatRepository,
        protected readonly ChatFactory $chatFactory,
        protected readonly ChatMessageFactory $chatMessageFactory,
        protected readonly OpenAiFacade $openAiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\ChatData $chatData
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat
     */
    public function create(ChatData $chatData): Chat
    {
        $chat = $this->chatFactory->create($chatData);
        $this->em->persist($chat);
        $this->em->flush();

        return $chat;
    }

    /**
     * @param string $identifier
     * @return \Shopsys\FrameworkBundle\Model\Chat\Chat|null
     */
    public function getChatByIdentifier(string $identifier): ?Chat
    {
        $chat = $this->chatRepository->findByIdentifier($identifier);

        if ($chat === null) {
            throw new NotFoundHttpException(sprintf('Chat with identifier %s not found.', $identifier));
        }

        return $chat;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $chat = $this->chatRepository->findById($id);

        if (!$chat) {
            return;
        }

        $this->em->remove($chat);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    protected function addQuestion(Chat $chat, string $question): ChatMessage
    {
        $chatMessage = $this->chatMessageFactory->create($chat, $question);
        $this->em->persist($chatMessage);
        $this->em->flush();

        $chat->addMessage($chatMessage);
        $this->em->flush();

        return $chatMessage;
    }

    //TODO - function below should be in own service some like AiFacade - main AI service. For now are temporary here.

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\Chat $chat
     * @param string $question
     * @return \Shopsys\FrameworkBundle\Model\Chat\Message\ChatMessage
     */
    public function handleQuestion(Chat $chat, string $question): ChatMessage
    {
        $chatMessage = $this->addQuestion($chat, $question);

        //some factory for resolving AI service by Agent setup(model)
        $this->openAiFacade->handleQuestion($chatMessage);

        return $chatMessage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return string|null
     */
    public function createVectorStore(VectorStore $vectorStore): ?string
    {
        return $this->openAiFacade->createVectorStore($vectorStore);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @return bool
     */
    public function deleteVectorStore(VectorStore $vectorStore): bool
    {
        return $this->openAiFacade->deleteVectorStore($vectorStore);
    }

    /**
     * @return array<int, array{externalId: string, name: string}>
     */
    public function getAllVectorStoreResponses(): array
    {
        return $this->openAiFacade->getAllVectorStoreResponses();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore $vectorStore
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function exportProductToVectorStore(
        VectorStore $vectorStore,
        Product $product,
        DomainConfig $domainConfig,
    ): void {
        $payload = [];
        $payload['name'] = $product->getName($domainConfig->getLocale());
        $payload['description'] = $product->getDescription($domainConfig->getId());
        $payload['brand'] = $product->getBrand()->getName();
        $payload['categories'] = array_map(
            fn (Category $category) => $category->getName($domainConfig->getLocale()),
            $product->getCategoriesIndexedByDomainId()[$domainConfig->getId()],
        );
        $payload['catnum'] = $product->getCatnum();
        $payload['identifierKey'] = 'catnum';
        $payload['dataObject'] = 'product';

        //        d($payload);
        $this->openAiFacade->appendObjectToVectorStore($vectorStore, $payload);
    }

    //    public function exportDataObjectToVectorStore(VectorStore $vectorStore, object $dataObject, DomainConfig $domainConfig): void
    //    {
    //        /** @var array<string, string> $dataStructure */
    //        $dataStructure = $vectorStore->getDataStructure(); // např. ['name' => 'name', …]
    //
    //        if ($dataStructure === []) {
    //            // Není nic k exportu
    //            return;
    //        }
    //
    //        $propertyAccessor = $this->createPropertyAccessor();
    //
    //        $payload = [];
    //        foreach ($dataStructure as $vectorFieldName => $objectPropertyPath) {
    //            // Pokud není cesta čitelná, vložíme NULL, případně lze vyhodit výjimku
    //            $payload[$vectorFieldName] = $propertyAccessor->isReadable($dataObject, $objectPropertyPath)
    //                ? $propertyAccessor->getValue($dataObject, $objectPropertyPath)
    //                : null;
    //        }
    //
    //        // TODO: předat $payload dalšímu kroku (např. přes OpenAiFacade)
    //        // $this->openAiFacade->appendObjectToVectorStore($vectorStore, $payload);
    //
    //    }
    //
    //    /**
    //     * @param $value
    //     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
    //     * @return string
    //     */
    //    private function prepareValueForVectorStore($value, DomainConfig $domainConfig): string
    //    {
    //        if (is_string($value)) {
    //            return $value;
    //        }
    //
    //        if (is_array($value)) {
    //            if(array_key_exists($domainConfig->getLocale(), $value)) {
    //                return $value[$domainConfig->getLocale()];
    //            }
    //
    //            if (array_key_exists($domainConfig->getId(), $value)) {
    //                return $value[$domainConfig->getId()];
    //            }
    //
    //            return implode(' ', $value);
    //        }
    //
    //        return $value;
    //    }
    //
    //    private function createPropertyAccessor(): PropertyAccessorInterface
    //    {
    //        return PropertyAccess::createPropertyAccessor();
    //    }
}
