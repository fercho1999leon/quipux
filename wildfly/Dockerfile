# Base image
FROM jboss/wildfly:latest
 
# Maintainer
MAINTAINER "Fernando" "tecnico.tic@itred.edu.ec"
 
# Create user jota with password jota1234
RUN /opt/jboss/wildfly/bin/add-user.sh 1752349264 Root1996* --silent
 
# Add custom configuration file
#ADD standalone.xml /opt/jboss/wildfly/standalone/configuration/
 
# Add example.war to deployments
#ADD example.war /opt/jboss/wildfly/standalone/deployments/
 
# Run
CMD ["/opt/jboss/wildfly/bin/standalone.sh", "-b", "0.0.0.0", "-bmanagement", "0.0.0.0", "-c", "standalone.xml"]