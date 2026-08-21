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

        stage('Build Backend Test Image') {
            steps {
                dir('backend') {
                    sh "docker build -t ${APP_NAME}-backend-test ."
                }
            }
        }

        stage('Test Backend') {
            steps {
                sh "docker run -d --name test-db -e POSTGRES_DB=app -e POSTGRES_USER=app -e POSTGRES_PASSWORD=secret -p 5432:5432 postgres:15-alpine"
                sh "sleep 5"
                sh """
                    docker run --rm \
                        --network host \
                        -e DATABASE_URL="postgresql://app:secret@127.0.0.1:5432/app?server_version=15" \
                        -e APP_ENV=test \
                        -e APP_SECRET="test-secret" \
                        -e JWT_PASSPHRASE="test" \
                        -e JWT_SECRET_KEY="%kernel.project_dir%/config/jwt/private.pem" \
                        -e JWT_PUBLIC_KEY="%kernel.project_dir%/config/jwt/public.pem" \
                        ${APP_NAME}-backend-test \
                        sh -c "composer install --no-interaction --no-scripts && php bin/console doctrine:schema:create --no-interaction && php vendor/bin/phpunit --colors=never"
                """
            }
            post {
                always {
                    sh "docker stop test-db 2>/dev/null; docker rm test-db 2>/dev/null; true"
                    echo "Backend tests completados"
                }
            }
        }

        stage('Test Frontend') {
            steps {
                dir('frontend') {
                    sh """
                        docker run --rm \
                            -v "\$(pwd):/app" \
                            -w /app \
                            node:20-alpine \
                            sh -c "npm ci && npm test -- --passWithNoTests"
                    """
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
            steps {
                sh """
                    docker-compose -p crud down --remove-orphans || true
                    docker-compose -p crud up -d --build
                """
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
