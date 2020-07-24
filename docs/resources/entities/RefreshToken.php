<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken as BaseRefreshToken;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_refresh_token")
 */
class RefreshToken extends BaseRefreshToken
{
}
