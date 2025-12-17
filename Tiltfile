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

### PHP Service - FPM Low (pm=static, max_children=5, max_requests=500) ###

docker_build(
  'pdp-prep/php-fpm-low',
  '.',
  dockerfile='./infra/docker/Dockerfile.php-fpm-low',
  live_update=[
    sync('./shared', '/var/www/pdp/shared'),
  ],
)

k8s_yaml('./infra/k8s/php-service-fpm-low-deployment.yaml')
k8s_resource('php-service-fpm-low', labels="services")

### End of PHP Service - FPM Low ###

### PHP Service - FPM Mid (pm=dynamic, max_children=20, max_requests=750) ###

docker_build(
  'pdp-prep/php-fpm-mid',
  '.',
  dockerfile='./infra/docker/Dockerfile.php-fpm-mid',
  live_update=[
    sync('./shared', '/var/www/pdp/shared'),
  ],
)

k8s_yaml('./infra/k8s/php-service-fpm-mid-deployment.yaml')
k8s_resource('php-service-fpm-mid', labels="services")

### End of PHP Service - FPM Mid ###

### PHP Service - FPM High (pm=dynamic, max_children=50, max_requests=1000) ###

docker_build(
  'pdp-prep/php-fpm-high',
  '.',
  dockerfile='./infra/docker/Dockerfile.php-fpm-high',
  live_update=[
    sync('./shared', '/var/www/pdp/shared'),
  ],
)

k8s_yaml('./infra/k8s/php-service-fpm-high-deployment.yaml')
k8s_resource('php-service-fpm-high', labels="services")

### End of PHP Service - FPM High ###

### Nginx - FPM Low ###

k8s_yaml('./infra/k8s/nginx-fpm-low-deployment.yaml')
k8s_resource('nginx-fpm-low', port_forwards=8083, 
             resource_deps=['php-service-fpm-low'], labels="frontend")

### End of Nginx - FPM Low ###

### Nginx - FPM Mid ###

k8s_yaml('./infra/k8s/nginx-fpm-mid-deployment.yaml')
k8s_resource('nginx-fpm-mid', port_forwards=8084, 
             resource_deps=['php-service-fpm-mid'], labels="frontend")

### End of Nginx - FPM Mid ###

### Nginx - FPM High ###

k8s_yaml('./infra/k8s/nginx-fpm-high-deployment.yaml')
k8s_resource('nginx-fpm-high', port_forwards=8085, 
             resource_deps=['php-service-fpm-high'], labels="frontend")

### End of Nginx - FPM High ###

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
k8s_resource('api-gateway', labels="services")

### End of API Gateway ###

### Go Service ###

go_compile_cmd = 'cd services/go-service && CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o ../../build/go-service ./cmd/main.go'
if os.name == 'nt':
  go_compile_cmd = './infra/docker/go-service-build.bat'

local_resource(
  'go-service-compile',
  go_compile_cmd,
  deps=['./services/go-service'], 
  labels="compiles"
)

docker_build_with_restart(
  'pdp-prep/go-service',
  '.',
  entrypoint=['/app/build/go-service'],
  dockerfile='./infra/docker/Dockerfile.go-service-dev',
  only=[
    './build/go-service',
  ],
  live_update=[
    sync('./build', '/app/build'),
  ],
)

k8s_yaml('./infra/k8s/go-service-deployment.yaml')
k8s_resource('go-service', port_forwards=8090, labels="services")

### End of Go Service ###
