<?php

namespace App\Bitrix;

use App\Entity\Portal;
use Bitrix24\Bitrix24;
use Bitrix24\Exceptions\Bitrix24Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class B24App extends Bitrix24
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Portal
     */
    protected $portal;

    /**
     * B24App constructor.
     * @param bool $isSaveRawResponse
     * @param LoggerInterface|null $logger
     * @throws Bitrix24Exception
     */
    public function __construct($isSaveRawResponse = false, ?LoggerInterface $logger = null)
    {
        parent::__construct($isSaveRawResponse, $logger);

        $this->setApplicationScope(explode(',', $_ENV['B24_APPLICATION_SCOPE']));
        $this->setApplicationId($_ENV['B24_APPLICATION_ID']);
        $this->setApplicationSecret($_ENV['B24_APPLICATION_SECRET']);

        $this->setOnExpiredToken([$this, 'refreshAccessToken']);
    }

    /**
     * @inheritDoc
     */
    public function call($methodName, array $additionalParameters = [])
    {
        if ($this->portal === null) {
            $this->fetchAccessToken();
        }

        return parent::call($methodName, $additionalParameters);
    }

    /**
     * @throws Bitrix24Exception
     * @throws \Bitrix24\Exceptions\Bitrix24ApiException
     * @throws \Bitrix24\Exceptions\Bitrix24EmptyResponseException
     * @throws \Bitrix24\Exceptions\Bitrix24IoException
     * @throws \Bitrix24\Exceptions\Bitrix24MethodNotFoundException
     * @throws \Bitrix24\Exceptions\Bitrix24PaymentRequiredException
     * @throws \Bitrix24\Exceptions\Bitrix24PortalDeletedException
     * @throws \Bitrix24\Exceptions\Bitrix24PortalRenamedException
     * @throws \Bitrix24\Exceptions\Bitrix24TokenIsExpiredException
     * @throws \Bitrix24\Exceptions\Bitrix24TokenIsInvalidException
     * @throws \Bitrix24\Exceptions\Bitrix24WrongClientException
     * @throws \Exception
     */
    public function fetchAccessToken(): void
    {
        $repository = $this->entityManager->getRepository(Portal::class);
        $this->portal = $repository->find(1);

        if ($this->portal === null) {
            throw new \RuntimeException('No access token found');
        }

        $this->setDomain($this->portal->getDomain());
        $this->setMemberId($this->portal->getMemberId());
        $this->setAccessToken($this->portal->getAccessToken());
        $this->setRefreshToken($this->portal->getRefreshToken());

        if ($this->portal->getExpires() < new \DateTime('now')) {
            $this->refreshAccessToken();
        }
    }

    /**
     * @throws Bitrix24Exception
     * @throws \Bitrix24\Exceptions\Bitrix24ApiException
     * @throws \Bitrix24\Exceptions\Bitrix24EmptyResponseException
     * @throws \Bitrix24\Exceptions\Bitrix24IoException
     * @throws \Bitrix24\Exceptions\Bitrix24MethodNotFoundException
     * @throws \Bitrix24\Exceptions\Bitrix24PaymentRequiredException
     * @throws \Bitrix24\Exceptions\Bitrix24PortalDeletedException
     * @throws \Bitrix24\Exceptions\Bitrix24PortalRenamedException
     * @throws \Bitrix24\Exceptions\Bitrix24TokenIsExpiredException
     * @throws \Bitrix24\Exceptions\Bitrix24TokenIsInvalidException
     * @throws \Bitrix24\Exceptions\Bitrix24WrongClientException
     * @throws \Exception
     */
    protected function refreshAccessToken(): void
    {
        $this->setRedirectUri('https://' . $_ENV['APP_DOMAIN'] . '/install');
        $result = $this->getNewAccessToken();

        if ($result['member_id'] === $this->portal->getMemberId()) {
            $userData = [
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
            ];
            $this->persistPortalData($userData);

            //saving for current run
            $this->setAccessToken($this->portal->getAccessToken());
            $this->setRefreshToken($this->portal->getRefreshToken());
        } else {
            throw new Bitrix24Exception('Wrong member_id given');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function addNewPortal(array $data): void
    {
        if (isset(
            $data['domain'],
            $data['access_token'],
            $data['refresh_token'],
            $data['member_id'])
        ) {
            $repository = $this->entityManager->getRepository(Portal::class);
            $this->portal = $repository->findOneByMemberId($data['member_id']);
            $this->persistPortalData($data);
        } else {
            throw new \RuntimeException('Invalid user data given');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function persistPortalData(array $data): void
    {
        if (isset($data['domain'])) {
            $this->portal->setDomain($data['domain']);
        }

        if (isset($data['access_token'])) {
            $this->portal->setAccessToken($data['access_token']);
        }

        if (isset($data['refresh_token'])) {
            $this->portal->setRefreshToken($data['refresh_token']);
        }

        if (isset($data['member_id'])) {
            $this->portal->setMemberId($data['member_id']);
        }

        //setting changed date
        $changed = new \DateTime('now');
        $this->portal->setChanged($changed);

        //setting expires date
        $expires = clone $changed;
        $duration = 3600;
        if (isset($data['expires_in']) && (int)$data['expires_in'] < 1) {
            $duration = (int)$data['expires_in'];
        }
        $expires->add(new \DateInterval('PT' . $duration . 'S'));
        $this->portal->setExpires($expires);

        $this->entityManager->persist($this->portal);
        $this->entityManager->flush();
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}