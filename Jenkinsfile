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
                        sh -c "php bin/console doctrine:schema:create --force && php vendor/bin/phpunit --colors=never"
                """
            }
            post {
                always {
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
            when {
                branch 'main'
            }
            steps {
                input message: 'Deploy a produccion?', ok: 'Deploy'
                sh """
                    cd ~/JENKINS
                    git pull
                    docker-compose down
                    docker-compose up -d --build
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
