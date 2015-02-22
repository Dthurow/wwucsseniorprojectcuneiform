package cuneiform;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.PrintStream;

public class Parser {
    public static final String sourcePath  = "data/ur3_20140114_public.atf";
    public static final int    threadCount = Runtime.getRuntime().availableProcessors();
    public static final String dbHost = "jdbc:mysql://localhost/cuneiform";
    public static final String dbUser = "dingo";
    public static final String dbPass = "hungry!";
    public static final String alphabetFilePath = "FILLHERE"; //TODO: add relative file path

    public static void main(String[] args)
            throws IOException, InterruptedException {

    	// Register MySQL driver.
    	// See method body for details.
    	registerMySqlDriver();
    	
        BufferedReader reader = new BufferedReader(new FileReader(sourcePath));

        ParallelExtractor extractor = new ParallelExtractor(reader, dbHost, dbUser, dbPass);
        extractor.spawnThreads(threadCount);
        extractor.join();

        try (PrintStream ps = new PrintStream("sorted.txt")) {
            extractor.printStats(ps);
            extractor.printSorted(ps);
        }
        try (PrintStream ps = new PrintStream("no-matches.txt")) {
            extractor.printUnmatched(ps);
        }
        try (PrintStream ps = new PrintStream("matched-names.txt")) {
            extractor.printNames(ps);
        }
        extractor.printStats(System.out);
    }

    private static void registerMySqlDriver()
    {
    	/*
    	 * 	Steps to register MySQL JDBC under Ubuntu:
    	 * 		1. sudo apt-get install libmysql-java
    	 * 		2. Ensure that /usr/share/java/mysql.jar exists.
    	 * 		3. Register external .jar with your IDE.
    	 * 			In Eclipse:
    	 * 			Project --> Properties --> Java Build Path
    	 * 				--> Add External JARs...
    	 */
    	try
    	{
    		// Register the MySQL JDBC driver.
    		
    		Class.forName("com.mysql.jdbc.Driver");
    	}
    	catch (ClassNotFoundException e)
    	{
    		// The MySQL Java connector does not appear to be installed.
    		// There's not much we can do about that !
    		
    		System.err.println("Failed to register the JDBC driver.");
    		e.printStackTrace();
    	}
    }
}
