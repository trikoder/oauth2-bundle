<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken as BaseAccessToken;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_access_token")
 */
class AccessToken extends BaseAccessToken
{
}
