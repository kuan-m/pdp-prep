# Load the restart_process extension
load('ext://restart_process', 'docker_build_with_restart')

### K8s Config ###
k8s_yaml('./infra/k8s/app-config.yaml')
### End of K8s Config ###

### PHP Service - OPcache Only (без JIT) ###

docker_build(
  'pdp-prep/php-opcache-only',
  '.',
  dockerfile='./infra/docker/Dockerfile.opcache-only',
  live_update=[
    sync('./shared/3_jit_opcache', '/var/www/pdp/shared/3_jit_opcache'),
    sync('./shared/1_lifecycle', '/var/www/pdp/shared/1_lifecycle'),
    sync('./shared/2_php_fpm', '/var/www/pdp/shared/2_php_fpm'),
  ],
)

k8s_yaml('./infra/k8s/php-service-opcache-only-deployment.yaml')
k8s_resource('php-service-opcache-only', labels="services")

### End of PHP Service - OPcache Only ###

### PHP Service - OPcache + JIT ###

docker_build(
  'pdp-prep/php-opcache-jit',
  '.',
  dockerfile='./infra/docker/Dockerfile.opcache-jit',
  live_update=[
    sync('./shared/3_jit_opcache', '/var/www/pdp/shared/3_jit_opcache'),
    sync('./shared/1_lifecycle', '/var/www/pdp/shared/1_lifecycle'),
    sync('./shared/2_php_fpm', '/var/www/pdp/shared/2_php_fpm'),
  ],
)

k8s_yaml('./infra/k8s/php-service-opcache-jit-deployment.yaml')
k8s_resource('php-service-opcache-jit', labels="services")

### End of PHP Service - OPcache + JIT ###

### PHP Service - No OPcache ###

docker_build(
  'pdp-prep/php-no-opcache',
  '.',
  dockerfile='./infra/docker/Dockerfile.no-opcache',
  live_update=[
    sync('./shared/3_jit_opcache', '/var/www/pdp/shared/3_jit_opcache'),
    sync('./shared/1_lifecycle', '/var/www/pdp/shared/1_lifecycle'),
    sync('./shared/2_php_fpm', '/var/www/pdp/shared/2_php_fpm'),
  ],
)

k8s_yaml('./infra/k8s/php-service-no-opcache-deployment.yaml')
k8s_resource('php-service-no-opcache', labels="services")

### End of PHP Service - No OPcache ###

### Nginx - OPcache Only ###

k8s_yaml('./infra/k8s/nginx-opcache-only-deployment.yaml')
k8s_resource('nginx-opcache-only', port_forwards=8080, 
             resource_deps=['php-service-opcache-only'], labels="frontend")

### End of Nginx - OPcache Only ###

### Nginx - OPcache + JIT ###

k8s_yaml('./infra/k8s/nginx-opcache-jit-deployment.yaml')
k8s_resource('nginx-opcache-jit', port_forwards=8081, 
             resource_deps=['php-service-opcache-jit'], labels="frontend")

### End of Nginx - OPcache + JIT ###

### Nginx - No OPcache ###

k8s_yaml('./infra/k8s/nginx-no-opcache-deployment.yaml')
k8s_resource('nginx-no-opcache', port_forwards=8082, 
             resource_deps=['php-service-no-opcache'], labels="frontend")

### End of Nginx - No OPcache ###

### Web Frontend ###

docker_build(
  'pdp-prep/web',
  '.',
  dockerfile='./infra/docker/Dockerfile.web',
  live_update=[
    sync('./web', '/usr/share/nginx/pdp'),
  ],
)

k8s_yaml('./infra/k8s/web-deployment.yaml')
k8s_resource('web', port_forwards=3000, labels="frontend")

### End of Web Frontend ###

### API Gateway ###

docker_build(
  'pdp-prep/api-gateway',
  '.',
  dockerfile='./infra/docker/Dockerfile.api-gateway',
  live_update=[
    sync('./services/api-gateway/api.php', '/var/www/pdp/api.php'),
  ],
)

docker_build(
  'pdp-prep/api-gateway-nginx',
  '.',
  dockerfile='./infra/docker/Dockerfile.api-gateway-nginx',
)

k8s_yaml('./infra/k8s/api-gateway-deployment.yaml')
k8s_resource('api-gateway', resource_deps=['web'], labels="services")

### End of API Gateway ###
