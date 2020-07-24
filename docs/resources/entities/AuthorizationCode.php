<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode as BaseAuthorizationCode;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_authorization_code")
 */
class AuthorizationCode extends BaseAuthorizationCode
{
}
