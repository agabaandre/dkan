import groovy.json.JsonOutput

pipeline {
    agent any
    options {
        ansiColor 'xterm'
        skipDefaultCheckout(true)
    }
    environment {
        PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
        USER = 'jenkins'
        DKTL_VERSION = 'localdkan'
        // DKTL_SLUG = "$BRANCH_NAME"
    }
    stages {
        stage('Setup environment') {
            when { changeRequest() }
            steps {
                script {
                    try {
                        sh "dktl down" 
                    } catch (err) {
                        echo "DKTL not present; skipping"
                    }
                }
                sh "rm -rf *"
                dir("dkan") {
                    checkout([
                        $class: 'GitSCM',
                        branches: [[name: "refs/heads/${CHANGE_BRANCH}"]]
                    ])                
                }
                sh "curl -O -L https://github.com/GetDKAN/dkan-tools/archive/${DKTL_VERSION}.zip"
                sh "unzip ${DKTL_VERSION}.zip && mv dkan-tools-${DKTL_VERSION} dkan-tools && rm ${DKTL_VERSION}.zip"
            }
        }
        // stage('Initialize DKAN site') {
        //     when { changeRequest() }
        //     steps {
        //         sh "dktl init --dkan-local --dkan dev-${CHANGE_BRANCH}"
        //     }
        // }
        // stage('Build demo') {
        //     when { changeRequest() }
        //     steps {
        //         sh "dktl demo"
        //     }
        // }
    }
    post {
        success {
            script {
                gitCommitMessage = sh(returnStdout: true, script: 'git log -1 --pretty=%B').trim()
                currentBuild.description = "${gitCommitMessage}"
            }
        }
    }
}

/**
 * Report build status to github.
 *
 * @param message Message for status description
 * @param target_url URL of the QA site we're building
 * @param state State to report to Github (e.g. "success")
 */
void setBuildStatus(String message, String target_url, String state) {
    withCredentials([string(credentialsId: 'isdapps-github-api-token',
			  variable: 'GITHUB_API_TOKEN')]) {
	def url = "https://api.github.com/repos/isdapps/adc-code/statuses/$GIT_COMMIT?access_token=${GITHUB_API_TOKEN}"
	def data = [
	    target_url: target_url,
	    state: state,
	    description: message,
	    context: "continuous-integration/jenkins/build-status"
	]
	def payload = JsonOutput.toJson(data)
	sh "curl -X POST -H 'Content-Type: application/json' -d '${payload}' ${url}"
    }
}