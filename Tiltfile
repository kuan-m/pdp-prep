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
    sync('./shared', '/var/www/pdp/shared'),
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
    sync('./shared', '/var/www/pdp/shared'),
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
    sync('./shared', '/var/www/pdp/shared'),
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

### PHP Service - FPM Default (pm=static, max_children=5, max_requests=500) ###

docker_build(
  'pdp-prep/php-fpm-default',
  '.',
  dockerfile='./infra/docker/Dockerfile.php-fpm-default',
  live_update=[
    sync('./shared', '/var/www/pdp/shared'),
  ],
)

k8s_yaml('./infra/k8s/php-service-fpm-default-deployment.yaml')
k8s_resource('php-service-fpm-default', labels="services")

### End of PHP Service - FPM Default ###

### PHP Service - FPM Aggressive (pm=dynamic, max_children=50, max_requests=1000) ###

docker_build(
  'pdp-prep/php-fpm-aggressive',
  '.',
  dockerfile='./infra/docker/Dockerfile.php-fpm-aggressive',
  live_update=[
    sync('./shared', '/var/www/pdp/shared'),
  ],
)

k8s_yaml('./infra/k8s/php-service-fpm-aggressive-deployment.yaml')
k8s_resource('php-service-fpm-aggressive', labels="services")

### End of PHP Service - FPM Aggressive ###

### Nginx - FPM Default ###

k8s_yaml('./infra/k8s/nginx-fpm-default-deployment.yaml')
k8s_resource('nginx-fpm-default', port_forwards=8083, 
             resource_deps=['php-service-fpm-default'], labels="frontend")

### End of Nginx - FPM Default ###

### Nginx - FPM Aggressive ###

k8s_yaml('./infra/k8s/nginx-fpm-aggressive-deployment.yaml')
k8s_resource('nginx-fpm-aggressive', port_forwards=8084, 
             resource_deps=['php-service-fpm-aggressive'], labels="frontend")

### End of Nginx - FPM Aggressive ###

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
