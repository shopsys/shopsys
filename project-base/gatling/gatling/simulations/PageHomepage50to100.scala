package performance

import scala.concurrent.duration._
import io.gatling.core.Predef._
import io.gatling.http.Predef._
import io.gatling.jdbc.Predef._

class PageHomepage50to100 extends Simulation {
    private val baseUrl = System.getProperty("baseUrl");
    private val authLoginName = System.getProperty("authLoginName");
    private val authPassword = System.getProperty("authPassword");

    val httpProtocol = http
        .baseUrl(baseUrl)
        .acceptHeader("text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8")
        .doNotTrackHeader("1")
        .acceptLanguageHeader("en-US,en;q=0.5")
        .acceptEncodingHeader("gzip, deflate")
        .userAgentHeader("Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0")
        .basicAuth(authLoginName, authPassword)

    val scn = scenario("Homepage 50→100 Users")
        .exec(http("homepage_50_to_100")
        .get("/"))

    setUp(
      scn.inject(
        rampUsers(50) during(20.seconds),            // Ramp up to 50 users over 20s
        constantUsersPerSec(100) during(25.seconds), // Stay at 100 users for 25s
        rampUsers(0) during(15.seconds)              // Ramp down to 0 over 15s
      ).protocols(httpProtocol)
    )
}