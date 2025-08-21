<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use App\Contracts\NotificationServiceInterface;
use App\Contracts\NotificationSenderInterface;
use App\Contracts\NotificationChannelManagerInterface;
use App\Contracts\SimuladoRepositoryInterface;
use App\Contracts\SimuladoReaderInterface;
use App\Contracts\SimuladoWriterInterface;
use App\Contracts\CacheServiceInterface;
use App\Contracts\CacheClearerInterface;
use App\Contracts\CacheKeyGeneratorInterface;

class InterfaceSegregationPrincipleTest extends TestCase
{
    /**
     * Test that notification interfaces follow ISP
     */
    public function test_notification_interfaces_follow_isp()
    {
        // Test that specific interfaces have focused responsibilities
        $senderReflection = new ReflectionClass(NotificationSenderInterface::class);
        $channelManagerReflection = new ReflectionClass(NotificationChannelManagerInterface::class);
        
        // NotificationSenderInterface should only have send method
        $senderMethods = $senderReflection->getMethods();
        $this->assertCount(1, $senderMethods);
        $this->assertEquals('send', $senderMethods[0]->getName());
        
        // NotificationChannelManagerInterface should have channel management methods
        $channelMethods = $channelManagerReflection->getMethods();
        $this->assertCount(4, $channelMethods);
        
        $channelMethodNames = array_map(fn($method) => $method->getName(), $channelMethods);
        $this->assertContains('addChannel', $channelMethodNames);
        $this->assertContains('removeChannel', $channelMethodNames);
        $this->assertContains('getAvailableChannels', $channelMethodNames);
        $this->assertContains('hasChannel', $channelMethodNames);
        
        // NotificationServiceInterface should extend both specific interfaces
        $serviceReflection = new ReflectionClass(NotificationServiceInterface::class);
        $interfaces = $serviceReflection->getInterfaceNames();
        $this->assertContains(NotificationSenderInterface::class, $interfaces);
        $this->assertContains(NotificationChannelManagerInterface::class, $interfaces);
    }
    
    /**
     * Test that simulado repository interfaces follow ISP
     */
    public function test_simulado_repository_interfaces_follow_isp()
    {
        // Test that reader interface only has read methods
        $readerReflection = new ReflectionClass(SimuladoReaderInterface::class);
        $readerMethods = $readerReflection->getMethods();
        
        $readerMethodNames = array_map(fn($method) => $method->getName(), $readerMethods);
        
        // All reader methods should be read operations
        $readOperations = ['findAll', 'findPaginated', 'findById', 'findByCategory', 'findActive', 'findArchived'];
        foreach ($readOperations as $operation) {
            $this->assertContains($operation, $readerMethodNames);
        }
        
        // Test that writer interface only has write methods
        $writerReflection = new ReflectionClass(SimuladoWriterInterface::class);
        $writerMethods = $writerReflection->getMethods();
        
        $writerMethodNames = array_map(fn($method) => $method->getName(), $writerMethods);
        
        // All writer methods should be write operations
        $writeOperations = ['create', 'update', 'delete'];
        foreach ($writeOperations as $operation) {
            $this->assertContains($operation, $writerMethodNames);
        }
        
        // SimuladoRepositoryInterface should extend both specific interfaces
        $repositoryReflection = new ReflectionClass(SimuladoRepositoryInterface::class);
        $interfaces = $repositoryReflection->getInterfaceNames();
        $this->assertContains(SimuladoReaderInterface::class, $interfaces);
        $this->assertContains(SimuladoWriterInterface::class, $interfaces);
    }
    
    /**
     * Test that cache interfaces follow ISP
     */
    public function test_cache_interfaces_follow_isp()
    {
        // Test that clearer interface only has clear methods
        $clearerReflection = new ReflectionClass(CacheClearerInterface::class);
        $clearerMethods = $clearerReflection->getMethods();
        
        $clearerMethodNames = array_map(fn($method) => $method->getName(), $clearerMethods);
        
        // All clearer methods should be clear operations
        $clearOperations = ['clearSimuladoCache', 'clearUserSimuladoCache', 'clearCategoryCache', 'clearAllCache'];
        foreach ($clearOperations as $operation) {
            $this->assertContains($operation, $clearerMethodNames);
        }
        
        // Test that key generator interface only has generate methods
        $generatorReflection = new ReflectionClass(CacheKeyGeneratorInterface::class);
        $generatorMethods = $generatorReflection->getMethods();
        
        $generatorMethodNames = array_map(fn($method) => $method->getName(), $generatorMethods);
        
        // All generator methods should be generate operations
        $generateOperations = ['generateSimuladoCacheKey', 'generateUserCacheKey', 'generateCategoryCacheKey'];
        foreach ($generateOperations as $operation) {
            $this->assertContains($operation, $generatorMethodNames);
        }
        
        // CacheServiceInterface should extend both specific interfaces
        $cacheReflection = new ReflectionClass(CacheServiceInterface::class);
        $interfaces = $cacheReflection->getInterfaceNames();
        $this->assertContains(CacheClearerInterface::class, $interfaces);
        $this->assertContains(CacheKeyGeneratorInterface::class, $interfaces);
    }
    
    /**
     * Test that interfaces are cohesive (methods are related)
     */
    public function test_interfaces_are_cohesive()
    {
        // Test NotificationSenderInterface cohesion
        $senderReflection = new ReflectionClass(NotificationSenderInterface::class);
        $this->assertTrue($senderReflection->hasMethod('send'));
        
        // Test NotificationChannelManagerInterface cohesion
        $channelManagerReflection = new ReflectionClass(NotificationChannelManagerInterface::class);
        $channelMethods = ['addChannel', 'removeChannel', 'getAvailableChannels', 'hasChannel'];
        foreach ($channelMethods as $method) {
            $this->assertTrue($channelManagerReflection->hasMethod($method));
        }
        
        // Test SimuladoReaderInterface cohesion
        $readerReflection = new ReflectionClass(SimuladoReaderInterface::class);
        $readMethods = ['findAll', 'findPaginated', 'findById', 'findByCategory', 'findActive', 'findArchived'];
        foreach ($readMethods as $method) {
            $this->assertTrue($readerReflection->hasMethod($method));
        }
        
        // Test SimuladoWriterInterface cohesion
        $writerReflection = new ReflectionClass(SimuladoWriterInterface::class);
        $writeMethods = ['create', 'update', 'delete'];
        foreach ($writeMethods as $method) {
            $this->assertTrue($writerReflection->hasMethod($method));
        }
    }
    
    /**
     * Test that clients can depend on specific interfaces they need
     */
    public function test_clients_can_depend_on_specific_interfaces()
    {
        // A client that only needs to send notifications should be able to depend on NotificationSenderInterface
        $this->assertTrue(interface_exists(NotificationSenderInterface::class));
        
        // A client that only needs to manage channels should be able to depend on NotificationChannelManagerInterface
        $this->assertTrue(interface_exists(NotificationChannelManagerInterface::class));
        
        // A client that only needs to read simulados should be able to depend on SimuladoReaderInterface
        $this->assertTrue(interface_exists(SimuladoReaderInterface::class));
        
        // A client that only needs to write simulados should be able to depend on SimuladoWriterInterface
        $this->assertTrue(interface_exists(SimuladoWriterInterface::class));
        
        // A client that only needs to clear cache should be able to depend on CacheClearerInterface
        $this->assertTrue(interface_exists(CacheClearerInterface::class));
        
        // A client that only needs to generate cache keys should be able to depend on CacheKeyGeneratorInterface
        $this->assertTrue(interface_exists(CacheKeyGeneratorInterface::class));
    }
}