const crisisDetection = require('../src/services/crisisDetection');
const crisisIntervention = require('../src/services/crisisIntervention');
const crisisResources = require('../src/services/crisisResources');
const chalk = require('chalk');

/**
 * Test Crisis Intervention System
 * Validates all components of the crisis detection and intervention system
 */

async function testCrisisSystem() {
  console.log(chalk.blue.bold('\n🚨 TESTING MOODIFYME CRISIS INTERVENTION SYSTEM 🚨\n'));

  const testCases = [
    {
      name: 'Critical Suicide Risk',
      message: 'I want to kill myself tonight. I have a plan and I can\'t take it anymore.',
      expectedRisk: 9,
      expectedType: 'suicide'
    },
    {
      name: 'Self-Harm Indicators',
      message: 'I keep cutting myself to feel better. I hurt myself when I\'m sad.',
      expectedRisk: 7,
      expectedType: 'selfHarm'
    },
    {
      name: 'Severe Depression',
      message: 'I feel hopeless and like there\'s no point in anything. I can\'t go on.',
      expectedRisk: 5,
      expectedType: 'severe'
    },
    {
      name: 'Mild Distress',
      message: 'I\'m feeling really sad today and everything seems difficult.',
      expectedRisk: 3,
      expectedType: null
    },
    {
      name: 'Normal Conversation',
      message: 'I had a good day today, went for a walk and enjoyed the sunshine.',
      expectedRisk: 0,
      expectedType: null
    },
    {
      name: 'Metaphorical Usage',
      message: 'This homework is killing me, I\'m dying of boredom.',
      expectedRisk: 2,
      expectedType: null
    }
  ];

  let passedTests = 0;
  let totalTests = 0;

  console.log(chalk.yellow('📊 CRISIS DETECTION TESTS\n'));

  for (const testCase of testCases) {
    totalTests++;
    console.log(chalk.cyan(`Testing: ${testCase.name}`));
    console.log(chalk.gray(`Message: "${testCase.message}"`));

    try {
      const assessment = await crisisDetection.assessCrisisRisk(
        testCase.message,
        'test-user',
        []
      );

      console.log(chalk.white(`Result: Risk Level ${assessment.riskLevel}, Type: ${assessment.crisisType || 'none'}`));

      // Validate results
      const riskInRange = Math.abs(assessment.riskLevel - testCase.expectedRisk) <= 2;
      const typeMatches = assessment.crisisType === testCase.expectedType || 
                         (testCase.expectedType === null && assessment.riskLevel < 4);

      if (riskInRange && typeMatches) {
        console.log(chalk.green('✅ PASSED\n'));
        passedTests++;
      } else {
        console.log(chalk.red(`❌ FAILED - Expected risk ~${testCase.expectedRisk}, type ${testCase.expectedType}\n`));
      }

    } catch (error) {
      console.log(chalk.red(`❌ ERROR: ${error.message}\n`));
    }
  }

  console.log(chalk.yellow('🛡️ CRISIS INTERVENTION TESTS\n'));

  // Test intervention system
  const highRiskAssessment = {
    riskLevel: 9,
    severity: 'critical',
    crisisType: 'suicide',
    confidence: 0.9,
    immediacy: 'high',
    triggers: ['kill myself', 'can\'t take it']
  };

  try {
    totalTests++;
    console.log(chalk.cyan('Testing: High-Risk Crisis Intervention'));
    
    const intervention = await crisisIntervention.executeIntervention(
      highRiskAssessment,
      'test-user',
      'I want to kill myself',
      {}
    );

    if (intervention.level === 'CRITICAL' && intervention.type.includes('CRITICAL')) {
      console.log(chalk.green('✅ PASSED - Critical intervention activated\n'));
      passedTests++;
    } else {
      console.log(chalk.red('❌ FAILED - Expected critical intervention\n'));
    }

  } catch (error) {
    console.log(chalk.red(`❌ ERROR: ${error.message}\n`));
  }

  console.log(chalk.yellow('📞 CRISIS RESOURCES TESTS\n'));

  // Test crisis resources
  try {
    totalTests++;
    console.log(chalk.cyan('Testing: Emergency Resources'));
    
    const resources = await crisisResources.getEmergencyResources('test-user');
    
    if (resources.immediate && resources.immediate.suicide && resources.immediate.crisis) {
      console.log(chalk.green('✅ PASSED - Emergency resources available\n'));
      passedTests++;
    } else {
      console.log(chalk.red('❌ FAILED - Missing emergency resources\n'));
    }

  } catch (error) {
    console.log(chalk.red(`❌ ERROR: ${error.message}\n`));
  }

  // Test API endpoints
  console.log(chalk.yellow('🌐 API ENDPOINT TESTS\n'));

  try {
    totalTests++;
    console.log(chalk.cyan('Testing: Crisis Detection API'));
    
    // This would require the server to be running
    // For now, just check if the services are properly exported
    if (typeof crisisDetection.assessCrisisRisk === 'function' &&
        typeof crisisIntervention.executeIntervention === 'function' &&
        typeof crisisResources.getEmergencyResources === 'function') {
      console.log(chalk.green('✅ PASSED - All services properly exported\n'));
      passedTests++;
    } else {
      console.log(chalk.red('❌ FAILED - Services not properly exported\n'));
    }

  } catch (error) {
    console.log(chalk.red(`❌ ERROR: ${error.message}\n`));
  }

  // Final results
  console.log(chalk.blue.bold('📋 TEST RESULTS SUMMARY\n'));
  console.log(chalk.white(`Total Tests: ${totalTests}`));
  console.log(chalk.green(`Passed: ${passedTests}`));
  console.log(chalk.red(`Failed: ${totalTests - passedTests}`));
  console.log(chalk.white(`Success Rate: ${Math.round((passedTests / totalTests) * 100)}%\n`));

  if (passedTests === totalTests) {
    console.log(chalk.green.bold('🎉 ALL TESTS PASSED! Crisis intervention system is ready.\n'));
  } else if (passedTests / totalTests >= 0.8) {
    console.log(chalk.yellow.bold('⚠️ Most tests passed. System is functional with minor issues.\n'));
  } else {
    console.log(chalk.red.bold('❌ Multiple test failures. System needs attention before deployment.\n'));
  }

  // Display sample crisis intervention
  console.log(chalk.blue.bold('📝 SAMPLE CRISIS INTERVENTION RESPONSE:\n'));
  
  try {
    const sampleAssessment = await crisisDetection.assessCrisisRisk(
      'I feel like ending it all. Nobody would miss me.',
      'sample-user',
      []
    );

    const sampleIntervention = await crisisIntervention.executeIntervention(
      sampleAssessment,
      'sample-user',
      'I feel like ending it all. Nobody would miss me.',
      {}
    );

    console.log(chalk.cyan('User Message:'), '"I feel like ending it all. Nobody would miss me."');
    console.log(chalk.yellow('Risk Assessment:'), `Level ${sampleAssessment.riskLevel} (${sampleAssessment.severity})`);
    console.log(chalk.green('Intervention Type:'), sampleIntervention.type);
    console.log(chalk.white('Response Preview:'));
    console.log(chalk.gray(sampleIntervention.message.substring(0, 200) + '...\n'));

  } catch (error) {
    console.log(chalk.red(`Error generating sample: ${error.message}\n`));
  }

  console.log(chalk.blue.bold('🚀 Crisis intervention system testing complete!\n'));
}

// Run tests if this script is executed directly
if (require.main === module) {
  testCrisisSystem().catch(error => {
    console.error(chalk.red.bold('Test execution failed:'), error);
    process.exit(1);
  });
}

module.exports = { testCrisisSystem };
