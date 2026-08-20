pipeline {
    agent any

    environment {
        APP_NAME = 'crud-app'
        DOCKER_REGISTRY = 'registry.example.com'
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
                        docker build -t ${APP_NAME}-backend-test -f Dockerfile .
                    '''
                }
            }
        }

        stage('Test Frontend') {
            steps {
                dir('frontend') {
                    sh '''
                        docker build -t ${APP_NAME}-frontend-test -f Dockerfile .
                    '''
                }
            }
        }

        stage('Build Backend') {
            steps {
                dir('backend') {
                    sh "docker build -t ${IMAGE_BACKEND} ."
                }
            }
        }

        stage('Build Frontend') {
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
                input message: 'Deploy a produccion?'
                sh '''
                    docker stop ${APP_NAME}-backend 2>/dev/null || true
                    docker stop ${APP_NAME}-frontend 2>/dev/null || true
                    docker rm ${APP_NAME}-backend 2>/dev/null || true
                    docker rm ${APP_NAME}-frontend 2>/dev/null || true

                    docker-compose up -d
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
