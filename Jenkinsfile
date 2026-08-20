pipeline {
    agent any

    environment {
        APP_NAME = 'crud-app'
        IMAGE_BACKEND = "${APP_NAME}-backend:${BUILD_NUMBER}"
        IMAGE_FRONTEND = "${APP_NAME}-frontend:${BUILD_NUMBER}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Test Backend') {
            steps {
                dir('backend') {
                    sh '''
                        docker run --rm \
                            -v "$(pwd):/app" \
                            -w /app \
                            php:8.2-cli \
                            sh -c "apt-get update -qq && apt-get install -y -qq unzip libpq-dev libicu-dev > /dev/null 2>&1 && docker-php-ext-install pdo_pgsql intl > /dev/null 2>&1 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer > /dev/null 2>&1 && composer install --no-interaction --quiet && php vendor/bin/phpunit"
                    '''
                }
            }
        }

        stage('Test Frontend') {
            steps {
                dir('frontend') {
                    sh '''
                        docker run --rm \
                            -v "$(pwd):/app" \
                            -w /app \
                            node:20-alpine \
                            sh -c "npm ci && npm test"
                    '''
                }
            }
            post {
                always {
                    echo "Frontend tests completados"
                }
            }
        }

        stage('Build Backend Image') {
            steps {
                dir('backend') {
                    sh "docker build -t ${IMAGE_BACKEND} ."
                }
            }
        }

        stage('Build Frontend Image') {
            steps {
                dir('frontend') {
                    sh "docker build -t ${IMAGE_FRONTEND} ."
                }
            }
        }

        stage('Deploy') {
            when {
                branch 'main'
            }
            steps {
                input message: 'Deploy a produccion?', ok: 'Deploy'
                sh '''
                    cd ~/JENKINS
                    git pull
                    docker-compose down
                    docker-compose up -d --build
                '''
            }
        }
    }

    post {
        success {
            echo "Pipeline completado exitosamente!"
        }
        failure {
            echo "El pipeline fallo!"
        }
        always {
            cleanWs()
        }
    }
}
