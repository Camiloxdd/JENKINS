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
                        docker build -t ${APP_NAME}-backend-test \
                            --target test \
                            -f Dockerfile .
                    '''
                }
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
