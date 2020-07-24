<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trikoder\Bundle\OAuth2Bundle\Model\Client as BaseClient;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_client")
 */
class Client extends BaseClient
{
}
