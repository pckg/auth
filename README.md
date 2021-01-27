# auth

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/8c8b27d58aaa405e9f15ee93f2712d54)](https://www.codacy.com/app/schtr4jh/auth?utm_source=github.com&utm_medium=referral&utm_content=pckg/auth&utm_campaign=badger)

![Build status](https://github.com/pckg/auth/workflows/Pckg%20Auth%20CI/badge.svg)

# Install
Add provider `\Pckg\Auth\Provider\Auth::class` to your App provider.

Add migration `\Pckg\Auth\Migration\CreateAuthTables::class` to your `migrations.php` config.

Migrate `$ php console yourApp migrator:install --repository=default --clean`

Create godfather `$ php console auth auth:create-godfather your@email.com`

Add provider to your config `'default' => [
                                     'type' => \Pckg\Auth\Service\Provider\Database::class,
                                     'entity' => \OpenCode\OAuth2\Entity\Users::class,
                                 ],`