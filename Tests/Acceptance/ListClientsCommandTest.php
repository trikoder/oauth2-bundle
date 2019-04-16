<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Acceptance;

use Symfony\Component\Console\Tester\CommandTester;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

final class ListClientsCommandTest extends AbstractAcceptanceTest
{
    public function testListClients()
    {
        $client = $this->fakeAClient('foobar');
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $output = $commandTester->getDisplay();

        $id = preg_quote($client->getIdentifier());
        $secret = preg_quote($client->getSecret());

        $this->assertRegExp("/^.*{$id}\s+{$secret}.*$/s", $output);
    }

    public function testListClientColumns()
    {
        $secret = 'What the jiminy crickets did you just flaming say about me, you little bozo? I’ll have you know I graduated top of my class in the Cub Scouts, and I’ve been involved in numerous secret camping trips in Wyoming, and I have over 300 confirmed knots. I am trained in first aid and I’m the top bandager in the entire US Boy Scouts (of America). You are nothing to me but just another friendly face. I will clean your wounds for you with precision the likes of which has never been seen before on this annual trip, mark my words. You think you can get away with saying those shenanigans to me over the Internet? Think again, finkle. As we speak I am contacting my secret network of MSN friends across the USA and your IP is being traced right now so you better prepare for the seminars, man. The storm that wipes out the pathetic little thing you call your bake sale. You’re frigging done, kid. I can be anywhere, anytime, and I can tie knots in over seven hundred ways, and that’s just with my bare hands. Not only am I extensively trained in road safety, but I have access to the entire manual of the United States Boy Scouts (of America) and I will use it to its full extent to train your miserable butt on the facts of the continents, you little schmuck. If only you could have known what unholy retribution your little “clever” comment was about to bring down upon you, maybe you would have held your silly tongue. But you couldn’t, you didn’t, and now you’re paying the price, you goshdarned sillyhead. I will throw leaves all over you and you will dance in them. You’re friggin done, kiddo.';
        $scopes = [
            new Scope('This 👈👉 is money snek. 🐍🐍💰💰 Upsnek ⬆⬆🔜🔜 in 7.123 7⃣ 1⃣2⃣3⃣ snekonds 🐍🐍 or you ✋✋ will NEVER ❌❌❌❌ get monies 💰💰 again Beware!! ✋✋❌❌ You😏😏 don\'t ❌❌ have much time!!🕛🕧🕐🕜🕑🕝🕝 You 😏😏 may never ❌❌get monies 💰💰🐍💰💰 again!!'),
            new Scope('To hit, or not to hit. Dost thou ever miss? I suppose it not. You have a male love interest, yet I would wager he does not kiss thee (Ye olde mwah). Furthermore; he will find another lass like he won\'t miss thee. And at the end of it all. He is going to skrrt, and he will hit that dab, as if he were the man known by the name of Wiz Khalifa'),
        ];

        $redirectUris = [
            new RedirectUri('http://redirect-uri-oh-my-oh-my'),
        ];

        $client =
            $this
                ->fakeAClient('foobar', $secret)
                ->setScopes(...$scopes)
                ->setRedirectUris(...$redirectUris)
        ;
        $this->getClientManager()->save($client);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--columns' => 'identifier,scope',
        ]);
        $output = $commandTester->getDisplay();

        $id = $client->getIdentifier();
        $scopes = implode(', ', $client->getScopes());
        $secret = $client->getSecret();
        $redirectUris = implode(', ', $client->getRedirectUris());

        $this->assertContains($id, $output);
        $this->assertContains($scopes, $output);
        $this->assertNotContains($secret, $output);
        $this->assertNotContains($redirectUris, $output);
    }

    public function testListFiltersClients()
    {
        $clientA = $this->fakeAClient('CLIENTE_A', 'SECRET_DE_A');
        $this->getClientManager()->save($clientA);

        $clientB =
            $this
                ->fakeAClient('DER_CLIENT_B', 'DAS_GEHEIMNIS_VON_B')
                ->setScopes(new Scope('EIN_GRANT_VON_B'))
        ;
        $this->getClientManager()->save($clientB);

        $command = $this->command();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--scope' => $clientB->getScopes(),
        ]);
        $output = $commandTester->getDisplay();

        $this->assertContains($clientB->getIdentifier(), $output);
        $this->assertContains($clientB->getSecret(), $output);
        $this->assertNotContains($clientA->getIdentifier(), $output);
        $this->assertNotContains($clientA->getSecret(), $output);
    }

    private function fakeAClient($identifier, $secret = 'quzbaz'): Client
    {
        return new Client($identifier, $secret);
    }

    private function getClientManager(): ClientManagerInterface
    {
        return
            $this
                ->client
                ->getContainer()
                ->get(ClientManagerInterface::class)
            ;
    }

    private function command()
    {
        return $this->application->find('trikoder:oauth2:list-clients');
    }
}
