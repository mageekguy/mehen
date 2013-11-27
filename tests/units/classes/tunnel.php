<?php

namespace mehen\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	mehen\tunnel as testedClass,
	mock\mehen\tunnel as mockedTestedClass
;

class tunnel extends atoum
{
	public function beforeTestMethod($method)
	{
		$test = $this;

		$this->getAssertionManager()->setHandler('then', function() use ($test) { $test->getTestAdapterStorage()->resetCalls(); return $test; });
	}

	public function testClassConstants()
	{
		$this
			->integer(testedClass::defaultSshPort)->isEqualTo(22)
			->integer(testedClass::defaultMaxTry)->isEqualTo(5)
			->integer(testedClass::sleepingDelay)->isEqualTo(1)
			->string(testedClass::sshTunnelingCommand)->isEqualTo('exec ssh -o StrictHostKeyChecking=no %1$s -p %2$s -N -L %3$s:%4$s:%5$s:%6$s %7$s@%8$s')
		;
	}

	public function test__construct()
	{
		$this
			->if($tunnel = new testedClass($sshHost = uniqid(), $sshUser = uniqid(), $localHost = uniqid(), $localPort = uniqid(), $remoteHost = uniqid(), $remotePort = uniqid()))
			->then
				->string($tunnel->getSshHost())->isEqualTo($sshHost)
				->string($tunnel->getSshUser())->isEqualTo($sshUser)
				->string($tunnel->getLocalHost())->isEqualTo($localHost)
				->string($tunnel->getLocalPort())->isEqualTo($localPort)
				->string($tunnel->getRemoteHost())->isEqualTo($remoteHost)
				->string($tunnel->getRemotePort())->isEqualTo($remotePort)
				->integer($tunnel->getSshPort())->isEqualTo(testedClass::defaultSshPort)
				->integer($tunnel->getMaxTry())->isEqualTo(testedClass::defaultMaxTry)
				->string($tunnel->getSshTunnelingCommand())->isEqualTo(sprintf(testedClass::sshTunnelingCommand, '', $tunnel->getSshPort(), $localHost, $localPort, $remoteHost, $remotePort, $sshUser, $sshHost))
		;
	}

	public function test__toString()
	{
		$this
			->if($tunnel = new testedClass(uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), uniqid()))
			->then
				->castToString($tunnel)->isEqualTo($tunnel->getSshTunnelingCommand())
		;
	}

	public function testSetSshPrivateKeyFile()
	{
		$this
			->if($tunnel = new testedClass($sshHost = uniqid(), $sshUser = uniqid(), $localHost = uniqid(), $localPort = uniqid(), $remoteHost = uniqid(), $remotePort = uniqid()))
			->then
				->object($tunnel->setSshPrivateKeyFile($file = uniqid()))->isIdenticalTo($tunnel)
				->string($tunnel->getSshTunnelingCommand())->isEqualTo(sprintf(testedClass::sshTunnelingCommand, '-i ' . $file, $tunnel->getSshPort(), $localHost, $localPort, $remoteHost, $remotePort, $sshUser, $sshHost))
		;
	}

	public function testIsOpen()
	{
		$this
			->given($tunnel = new testedClass($sshHost = uniqid(), $sshUser = uniqid(), $localHost = uniqid(), $localPort = uniqid(), $remoteHost = uniqid(), $remotePort = uniqid()))

			->if($this->function->stream_socket_client = false)
			->then
				->boolean($tunnel->isOpen())->isFalse()
				->function('stream_socket_client')->wasCalledWithArguments('tcp://' . $localHost . ':' . $localPort, null, null, 1)->once()

			->if(
				$this->function->stream_socket_client = $socket = uniqid(),
				$this->function->fclose->doesNothing()
			)
			->then
				->boolean($tunnel->isOpen())->isTrue()
				->function('fclose')->wasCalledWithArguments($socket)->once()
		;
	}

	public function testOpen()
	{
		$this
			->given(
				$this->mockGenerator->orphanize('close'),
				$tunnel = new mockedTestedClass($sshHost = uniqid(), $sshUser = uniqid(), $localHost = uniqid(), $localPort = uniqid(), $remoteHost = uniqid(), $remotePort = uniqid())
			)

			->if($this->calling($tunnel)->isOpen = true)
			->then
				->exception(function() use ($tunnel) { $tunnel->open(); })
					->isInstanceOf('mehen\tunnel\exception')
					->hasMessage('Tunnel is already open')

			->if(
				$this->calling($tunnel)->isOpen = false,
				$this->function->proc_open = false
			)
			->then
				->exception(function() use ($tunnel) { $tunnel->open(); })
					->isInstanceOf('mehen\tunnel\exception')
					->hasMessage('Unable to execute \'' . $tunnel . '\'')
				->function('proc_open')->wasCalledWithArguments($tunnel->getSshTunnelingCommand(), array(), array())->once()

			->if(
				$this->function->proc_open = uniqid(),
				$this->function->sleep->doesNothing()
			)
			->then
				->exception(function() use ($tunnel) { $tunnel->open(); })
					->isInstanceOf('mehen\tunnel\exception')
					->hasMessage('Unable to open tunnel')
				->mock($tunnel)
					->call('close')
					->after($this->function('proc_open')->wasCalledWithArguments($tunnel->getSshTunnelingCommand(), array(), array())->once())
						->once()

			->if(
				$this->calling($tunnel)->isOpen = true,
				$this->calling($tunnel)->isOpen[1] = false,
				$this->calling($tunnel)->isOpen[2] = false,
				$this->calling($tunnel)->isOpen[3] = false
			)
			->then
				->object($tunnel->open())->isIdenticalTo($tunnel)
				->function('proc_open')
					->wasCalledWithArguments($tunnel->getSshTunnelingCommand(), array(), array())
					->before($this->function('sleep')->wasCalledWithArguments(testedClass::sleepingDelay)->twice())
						->once()
				->mock($tunnel)->call('close')->never()
		;
	}

	public function testClose()
	{
		$this
			->given(
				$tunnel = new mockedTestedClass($sshHost = uniqid(), $sshUser = uniqid(), $localHost = uniqid(), $localPort = uniqid(), $remoteHost = uniqid(), $remotePort = uniqid())
			)

			->object($tunnel->close())->isIdenticalTo($tunnel)

			->if(
				$this->calling($tunnel)->isOpen = true,
				$this->calling($tunnel)->isOpen[1] = false,
				$this->function->proc_open = $resource = uniqid(),
				$this->function->proc_terminate->doesNothing(),
				$this->function->proc_close->doesNothing(),
				$tunnel->open()
			)
			->then
				->object($tunnel->close())->isIdenticalTo($tunnel)
				->function('proc_close')
					->wasCalledWithArguments($resource)
					->after($this->function('proc_terminate')->wasCalledWithArguments($resource, 9)->once())
						->once()
		;
	}
}
